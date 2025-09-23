<?php

namespace App\Http\Controllers;

use App\Models\AmortizationSchedules;
use App\Models\CoopProgram;
use App\Notifications\LoanOverdueNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class AmortizationScheduleController extends Controller
{
    public function generateSchedule($id)
    {
        $coopProgram = CoopProgram::with('program')->findOrFail($id);

        // Prevent duplicate schedule generation
        if ($coopProgram->amortizationSchedules()->exists()) {
            return back()->with('error', 'Amortization schedule already exists.');
        }

        // Calculate months to pay
        $monthsToPay = $coopProgram->program->term_months - $coopProgram->with_grace;
        if ($monthsToPay <= 0) {
            throw new \Exception('Invalid term and grace period.');
        }

        // Compute monthly installment
        $amountPerMonth = round($coopProgram->loan_ammount / $monthsToPay, 2);
        $startDate = \Carbon\Carbon::parse($coopProgram->start_date)->addMonths($coopProgram->with_grace);

        // Generate amortization schedule
        for ($i = 1; $i <= $monthsToPay; $i++) {
            $amountDue = ($i === $monthsToPay)
                ? $coopProgram->loan_ammount - ($amountPerMonth * ($monthsToPay - 1))
                : $amountPerMonth;

            AmortizationSchedules::create([
                'coop_program_id' => $coopProgram->id,
                'due_date' => $startDate->copy()->addMonths($i - 1),
                'installment' => $amountDue,
                'status' => 'Unpaid',
            ]);
        }

        return back()->with('success', 'Amortization schedule generated successfully!');
    }

    public function index()
    {
        $loans = CoopProgram::with(['program', 'cooperative', 'amortizationSchedules'])
            ->withCount('amortizationSchedules')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->cooperative?->id ?? 'N/A',      // cooperative ID
                'cooperative_name' => $p->cooperative?->name ?? 'N/A',
                'program_name' => $p->program?->name ?? 'N/A',
                'loan_amount' => $p->loan_ammount ?? 0,
                'status' => $p->program_status,
                'has_schedule' => $p->amortizationSchedules_count > 0,
                'coop_program_id' => $p->id,               // for action links
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
        $startDate = $firstSchedule?->due_date?->format('Y-m-d')
            ?? $coopProgram->start_date?->format('Y-m-d')
            ?? now()->format('Y-m-d');

        $gracePeriod = $coopProgram->with_grace ?? 0;
        $termMonths = max(($coopProgram->program?->term_months ?? 0) - $gracePeriod, 0);

        return Inertia::render('payments/amortization', [
            'coopProgram' => [
                'id' => $coopProgram->id,
                'cooperative_name' => $coopProgram->cooperative?->name ?? 'N/A',
                'program_name' => $coopProgram->program?->name ?? 'N/A',
                'loan_amount' => $coopProgram->loan_ammount ?? 0,
                'status' => $coopProgram->program_status ?? 'N/A',
                'schedules' => $coopProgram->amortizationSchedules->map(fn ($s) => [
                    'id' => $s->id,
                    'due_date' => $s->due_date instanceof \Carbon\Carbon ? $s->due_date->format('Y-m-d') : null,
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
        $schedule = AmortizationSchedules::with('coopProgram')->findOrFail($scheduleId);
        $coopProgram = $schedule->coopProgram;
        $programEmail = $coopProgram->email ?? null;

        if ($programEmail) {
            Notification::route('mail', $programEmail)
                ->notify(new LoanOverdueNotification($coopProgram));

            return back()->with('success', 'Overdue email sent to '.$programEmail);
        }

        return back()->with('error', 'No email found for this cooperative program.');
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
}
