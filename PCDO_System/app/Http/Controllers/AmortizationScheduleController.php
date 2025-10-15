<?php

namespace App\Http\Controllers;

use App\Models\AmortizationSchedules;
use App\Models\CoopProgram;
use App\Models\Resolved;
use App\Notifications\LoanOverdueNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class AmortizationScheduleController extends Controller
{
    public function generateSchedule($coopProgramId)
    {
        $coopProgram = CoopProgram::with('program')->findOrFail($coopProgramId);

        // Prevent duplicate generation
        if ($coopProgram->amortizationSchedules()->exists()) {
            return back()->with('error', 'Amortization schedule already exists.');
        }

        // Calculate months to pay
        $monthsToPay = $coopProgram->program->term_months - $coopProgram->with_grace;
        if ($monthsToPay <= 0) {
            return back()->with('error', 'Invalid term or grace period.');
        }

        // Compute monthly installment
        $amountPerMonth = round($coopProgram->loan_amount / $monthsToPay, 2);
        $startDate = Carbon::parse($coopProgram->start_date)->addMonths($coopProgram->with_grace);

        // Generate amortization schedule
        for ($i = 1; $i <= $monthsToPay; $i++) {
            $amountDue = ($i === $monthsToPay)
                ? $coopProgram->loan_amount - ($amountPerMonth * ($monthsToPay - 1))
                : $amountPerMonth;

            AmortizationSchedules::create([
                'coop_program_id' => $coopProgram->id,
                'due_date' => $startDate->copy()->addMonths($i - 1),
                'installment' => $amountDue,
                'status' => 'Unpaid',
            ]);
        }

        return redirect()
            ->route('programs.show', $coopProgram->program_id)
            ->with('success', 'Amortization schedule generated successfully!');
    }

    public function index()
    {
        $loans = CoopProgram::with(['program', 'cooperative', 'amortizationSchedules'])
            ->withCount('amortizationSchedules')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->cooperative?->id ?? 'N/A',
                'cooperative_name' => $p->cooperative?->name ?? 'N/A',
                'program_name' => $p->program?->name ?? 'N/A',
                'loan_amount' => $p->loan_amount ?? 0,
                'status' => $p->program_status ?? 'N/A', 
                'has_schedule' => $p->amortization_schedules_count > 0,
                'coop_program_id' => $p->id,
                'next_due_date' => optional(
                    $p->amortizationSchedules->where('status', '!=', 'Paid')->sortBy('due_date')->first()
                )->due_date?->format('Y-m-d') ?? 'N/A',
            ]);

        return Inertia::render('payments/index', [
            'coopPrograms' => $loans,
        ]);
    }

    public function show($coopProgramId)
    {
        $coopProgram = CoopProgram::with('cooperative', 'program', 'amortizationSchedules')
            ->findOrFail($coopProgramId);

        $firstSchedule = $coopProgram->amortizationSchedules()->orderBy('due_date')->first();
        $lastSchedule = $coopProgram->amortizationSchedules()->orderByDesc('due_date')->first();

        $startDate = optional($firstSchedule?->due_date)->format('Y-m-d')
            ?? optional($coopProgram->start_date)->format('Y-m-d')
            ?? now()->format('Y-m-d');

        $gracePeriod = $coopProgram->with_grace ?? 0;
        $termMonths = max(($coopProgram->program?->term_months ?? 0) - $gracePeriod, 0);

        return Inertia::render('payments/amortization', [
            'coopProgram' => [
                'id' => $coopProgram->id,
                'cooperative_name' => $coopProgram->cooperative?->name ?? 'N/A',
                'program_name' => $coopProgram->program?->name ?? 'N/A',
                'loan_amount' => $coopProgram->loan_amount ?? 0,
                'status' => $coopProgram->program_status ?? 'N/A',
                'program_status' => $coopProgram->program_status ?? 'N/A', //include explicitly
                'resolved' => $coopProgram->program_status === 'Resolved', //boolean helper
                'schedules' => $coopProgram->amortizationSchedules->map(fn ($s) => [
                    'id' => $s->id,
                    'due_date' => optional($s->due_date)->format('Y-m-d'),
                    'installment' => $s->installment ?? 0,
                    'penalty_amount' => $s->penalty_amount ?? 0,
                    'amount_paid' => $s->amount_paid ?? 0,
                    'balance' => $s->balance ?? $s->installment ?? 0,
                    'is_paid' => $s->status === 'Paid',
                    'status' => $s->status ?? 'Unpaid',
                ]),
                'start_date' => $startDate,
                'grace_period' => $gracePeriod,
                'term_months' => $termMonths,
                'expected_end_date' => optional($lastSchedule?->due_date)->format('Y-m-d'),
            ],
        ]);
    }

    public function markPaid(AmortizationSchedules $schedule)
    {
        $schedule->update([
            'status' => 'Paid',
            'date_paid' => now(),
            'balance' => 0,
            'amount_paid' => $schedule->installment + $schedule->penalty_amount,
        ]);

        return back()->with('success', 'Payment marked as paid.');
    }

    /**
     * Send overdue email notification for a schedule.
     */
    public function sendOverdueEmail($scheduleId)
    {
        $schedule = AmortizationSchedules::with('coopProgram.cooperative', 'pendingnotifications')->findOrFail($scheduleId);
        $programEmail = $schedule->coopProgram->email ?? null;

        if (! $programEmail) {
            return back()->with('error', 'No email found for this cooperative program.');
        }

        Notification::route('mail', $programEmail)
            ->notify(new LoanOverdueNotification($schedule));

        // ✅ Mark related pending notifications as processed
        $schedule->pendingnotifications()
            ->where('type', 'overdue')
            ->update(['processed' => 1]);

        return back()->with('success', 'Overdue email sent to '.$programEmail);
    }

    /**
     * Add or remove penalty from a schedule.
     */
    public function penalty(Request $request, AmortizationSchedules $schedule)
    {
        if ($request->has('add')) {
            $penalty = $schedule->installment * 0.01; // 1% penalty
            $schedule->penalty_amount += $penalty;
            $schedule->save();

            return back()->with('success', '1% penalty added to this overdue schedule.');
        }

        if ($request->has('remove')) {
            $schedule->penalty_amount = 0;
            $schedule->save();

            return back()->with('success', 'Penalty removed from this schedule.');
        }

        return back()->with('error', 'Invalid penalty action.');
    }

    /**
     * Note a payment amount (partial or full) for a schedule.
     */
    public function notePayment(Request $request, $id)
    {
        $schedule = AmortizationSchedules::findOrFail($id);

        $request->validate([
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $payment = $request->amount_paid;
        $remaining = $payment;

        // Get all schedules for this coop program ordered by due date
        $schedules = AmortizationSchedules::where('coop_program_id', $schedule->coop_program_id)
            ->orderBy('due_date', 'asc')
            ->get();

        foreach ($schedules as $sch) {
            if ($remaining <= 0) {
                break;
            }

            $due = $sch->installment + $sch->penalty_amount;
            $needed = $due - $sch->amount_paid;

            if ($needed > 0) {
                $toPay = min($remaining, $needed);
                $sch->amount_paid += $toPay;
                $sch->balance = $due - $sch->amount_paid;
                $remaining -= $toPay;

                if ($sch->balance <= 0) {
                    $sch->status = 'Paid';
                    $sch->balance = 0;
                    $sch->date_paid = now();
                } else {
                    $sch->status = 'Partial Paid';
                }

                $sch->save();
            } else {
                $sch->status = 'Paid';
                $sch->balance = 0;
                $sch->date_paid = $sch->date_paid ?? now();
                $sch->save();
            }
        }

        return back()->with('success', 'Payment noted successfully.');
    }

    public function markIncomplete($id)
    {
        $coopProgram = CoopProgram::findOrFail($id);
        $coopProgram->program_status = null;
        $coopProgram->save();

        return redirect()->back()->with('success', 'Program marked as Incomplete.');
    }

    public function markResolved(Request $request, $loanId)
    {
        $loan = CoopProgram::with('amortizationSchedules')->findOrFail($loanId);

        // Validate uploaded file
        $request->validate([
            'receipt' => 'required|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Directly read the file as binary (no storage or unlink)
        $binaryContent = file_get_contents($request->file('receipt')->getRealPath());

        // Save binary into database
        Resolved::create([
            'coop_program_id' => $loan->id,
            'file_content' => $binaryContent,
        ]);

        // Mark amortization schedules as resolved
        if ($loan->amortizationSchedules->count() > 0) {
            foreach ($loan->amortizationSchedules as $schedule) {
                $schedule->update([
                    'status' => 'Resolved',
                    'date_paid' => now(),
                    'balance' => 0,
                    'penalty_amount' => 0,
                ]);
            }
        }

        // Update main program status
        $loan->update(['program_status' => 'Resolved']);

        return redirect()
            ->back()
            ->with('success', 'Loan marked as resolved successfully!');
    }
}
