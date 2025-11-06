<?php

namespace App\Http\Controllers;

use App\Mail\CoopProgramEnrolled;
use App\Models\AmortizationSchedules;
use App\Models\Cooperative;
use App\Models\CoopProgram;
use App\Models\CoopProgramChecklist;
use App\Models\FinishedCoopProgramChecklist;
use App\Models\Notifications;
use App\Models\Programs;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ProgramController extends Controller
{
    // Display a listing of the programs with cooperative count.

    public function index()
    {
        $programs = Programs::withCount('coopProgram')->get();

        return inertia('programs/index', [
            'programs' => $programs->map(fn ($program) => [
                'id' => $program->id,
                'name' => $program->name,
                'cooperatives_count' => $program->coop_program_count,
            ]),
        ]);
    }

    // Show one program and its cooperatives.

    public function show($id)
    {
        $program = Programs::findOrFail($id);

        $cooperatives = CoopProgram::with('cooperative')
            ->where('program_id', $id)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn ($cp) => [
                'id' => $cp->cooperative->id,
                'name' => $cp->cooperative->name,
                'start_date' => $cp->start_date,
                'program_status' => $cp->program_status,
                'has_checklist' => $cp->checklist()->exists(),
                'has_amortization' => $cp->amortizationSchedules()->exists(),
                'coopProgramId' => $cp->id,
            ]);

        return inertia('programs/show', [
            'program' => $program,
            'cooperatives' => $cooperatives,
        ]);
    }

    public function createCooperative(Programs $program): Response
    {
        $cooperatives = Cooperative::whereDoesntHave('programs', function ($q) {
            $q->where('program_status', 'Ongoing');
        })
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('programs/create', [
            'program' => $program,
            'cooperatives' => $cooperatives,
        ]);
    }

    public function storeCooperative(Request $request, Programs $program)
    {
        $data = $request->validate([
            'cooperative_id' => 'required|exists:cooperatives,id',
            'project' => 'required|string|max:255',
        ]);

        $cooperative = Cooperative::findOrFail($data['cooperative_id']);

        // Same validation logic you had before...
        $ongoingPrograms = CoopProgram::where('coop_id', $cooperative->id)
            ->where('program_status', 'Ongoing')
            ->with('program')
            ->get();

        foreach ($ongoingPrograms as $ongoing) {
            if ($ongoing->program_id === $program->id) {
                return back()->withErrors(['program_id' => 'This program is already ongoing.']);
            }
            if ($program->name === 'LICAP' && $ongoing->program->name === 'LICAP') {
                return back()->withErrors(['program_id' => 'LICAP program already ongoing.']);
            }
            if ($program->name !== 'LICAP' && $ongoing->program->name !== 'LICAP') {
                return back()->withErrors(['program_id' => 'Cannot enroll in another non-LICAP program while one is ongoing.']);
            }
        }

        $coopProgram = CoopProgram::create([
            'coop_id' => $cooperative->id,
            'program_id' => $program->id,
            'project' => $data['project'],
            'start_date' => now(),
            'end_date' => now()->addMonths($program->term_months),
            'program_status' => 'Ongoing',
            'loan_amount' => null,
            'with_grace' => null,
        ]);

        //  Log notification of enrollment
        Notifications::create([
            'schedule_id' => null,
            'coop_id' => $cooperative->id,
            'type' => 'enrolled',
            'subject' => 'Cooperative Enrolled in Program',
            'body' => "The cooperative '{$cooperative->name}' has been enrolled in the '{$program->name}' program on ".now()->format('F j, Y').'.',
            'processed' => 1,
        ]);

        //  Send email notification if coop has an email
        if ($cooperative->coopDetail && $cooperative->coopDetail->email) {
            Mail::to($cooperative->coopDetail->email)
                ->send(new CoopProgramEnrolled($cooperative, $program));
        }

        //  Redirect to checklist show page
        return redirect()->route(
            'programs.cooperatives.checklist.show',
            [
                'program' => $program->id,
                'cooperative' => $cooperative->id,
            ]
        )->with('success', 'Program enrolled successfully. Notification logged.');
    }

    public function finalizeLoan(Request $request, Programs $program, Cooperative $cooperative)
    {
        $request->validate([
            'loan_amount' => 'required|numeric|min:1',
            'with_grace' => 'required|numeric',
        ]);

        $coopProgram = CoopProgram::where('program_id', $program->id)
            ->where('coop_id', $cooperative->id)
            ->orderby('id', 'desc')
            ->first();

        if (! $coopProgram) {
            return back()->withErrors(['loan_amount' => 'Program does not exist for this cooperative.']);
        }

        if ($request->loan_amount < $program->min_amount || $request->loan_amount > $program->max_amount) {
            return back()->withErrors([
                'loan_amount' => "Loan amount must be between ₱{$program->min_amount} and ₱{$program->max_amount}",
            ]);
        }

        //  Update coop program
        $coopProgram->update([
            'loan_amount' => $request->loan_amount,
            'with_grace' => $request->with_grace,
        ]);

        //  Auto-generate amortization schedule
        if (! $coopProgram->amortizationSchedules()->exists()) {
            $monthsToPay = $program->term_months - $coopProgram->with_grace;
            if ($monthsToPay <= 0) {
                return back()->withErrors(['loan_amount' => 'Invalid term or grace period.']);
            }

            $amountPerMonth = round($coopProgram->loan_amount / $monthsToPay, 2);
            $startDate = now()->addMonths($coopProgram->with_grace);

            for ($i = 1; $i <= $monthsToPay; $i++) {
                $amountDue = ($i === $monthsToPay)
                    ? $coopProgram->loan_amount - ($amountPerMonth * ($monthsToPay - 1))
                    : $amountPerMonth;

                AmortizationSchedules::create([
                    'coop_program_id' => $coopProgram->id,
                    'due_date' => $startDate->copy()->addMonthsNoOverflow($i),
                    'installment' => $amountDue,
                    'status' => 'Unpaid',
                ]);
            }

            //  Log Notification
            Notifications::create([
                'schedule_id' => null,
                'coop_id' => $cooperative->id,
                'type' => 'has_schedule',
                'subject' => 'Amortization Schedule Created',
                'body' => "The cooperative '{$cooperative->name}' has been issued an amortization schedule under the '{$program->name}' program. First due date: ".$startDate->format('F d, Y').'.',
                'processed' => 1,
            ]);

            //  Optional: Send Email
            $coopDetail = $cooperative->coopDetail;
            if ($coopDetail && $coopDetail->email) {
                $subject = 'Amortization Schedule Created';
                $body = "Dear {$cooperative->name},\n\nYour amortization schedule has been successfully generated under the program '{$program->name}'.\nYour first payment of ₱{$amountPerMonth} is due on ".$startDate->format('F d, Y').".\n\nThank you.";

                Mail::raw($body, function ($message) use ($coopDetail, $subject) {
                    $message->to($coopDetail->email)
                        ->subject($subject);
                });
            }
        }

        //  Redirect to the loan tracker page after everything
        return redirect()
            ->route('amortizations.show', $coopProgram->id)
            ->with('success', 'Loan finalized and amortization schedule generated successfully!');
    }

    public function archiveFinishedProgram($coopProgramId)
    {
        DB::transaction(function () use ($coopProgramId) {
            $coopProgram = CoopProgram::with('checklists.uploads')->findOrFail($coopProgramId);

            if ($coopProgram->program_status !== 'Finished' || $coopProgram->exported !== 1) {
                throw new \Exception('Program must be finished and exported before archiving.');
            }

            $finished = FinishedCoopProgram::create([
                'coop_id' => $coopProgram->coop_id,
                'program_id' => $coopProgram->program_id,
                'start_date' => $coopProgram->start_date,
                'end_date' => $coopProgram->end_date,
                'program_status' => $coopProgram->program_status,
                'loan_amount' => $coopProgram->loan_amount,
                'with_grace' => $coopProgram->with_grace,
                'email' => $coopProgram->email,
                'number' => $coopProgram->number,
                'exported' => true,
            ]);

            foreach ($coopProgram->checklists as $checklist) {
                foreach ($checklist->uploads as $upload) {
                    FinishedCoopProgramChecklist::create([
                        'finished_coop_program_id' => $finished->id,
                        'checklist_id' => $checklist->checklist_id,
                        'is_completed' => true,
                        'file_name' => $upload->file_name,
                        'mime_type' => $upload->mime_type,
                        'file_content' => $upload->file_content,
                    ]);
                }
            }

            CoopProgramChecklist::where('coop_program_id', $coopProgram->id)->delete();
            $coopProgram->delete();
        });

        return redirect()->route('programs.index')->with('success', 'Program archived successfully!');
    }

    public function monthlyReport(Request $request)
    {
        $selectedMonth = $request->input('month')
            ? Carbon::parse($request->input('month'))
            : Carbon::now();

        $monthStart = $selectedMonth->copy()->startOfMonth();
        $monthEnd = $selectedMonth->copy()->endOfMonth();

        // 1️⃣ Registered Cooperatives this month
        $registeredCoops = Cooperative::whereBetween('created_at', [$monthStart, $monthEnd])
            ->select('id', 'name', 'created_at')
            ->get();

        // 2️⃣ Programs with cooperative activities (Ongoing + Finished)
        $programs = Programs::with(['coopProgram.cooperative', 'coopProgram.amortizationSchedules'])
            ->get()
            ->map(function ($program) use ($monthStart, $monthEnd) {
                $coopPrograms = $program->coopProgram;
                $ongoing = $coopPrograms->whereIn('program_status', ['Ongoing', 'Finished', 'Resolved']);

                $programSummary = [
                    'program_name' => $program->name,
                    'cooperatives' => [],
                    'has_amortization' => [],
                    'checklist_only' => [],
                ];

                foreach ($ongoing as $cp) {
                    $coop = $cp->cooperative;
                    if (! $coop) {
                        continue;
                    }

                    $hasAmortization = $cp->amortizationSchedules->count() > 0;

                    // 🧾 Gather amortization payments in selected month
                    $payments = $cp->amortizationSchedules
                        ->filter(function ($a) use ($monthStart, $monthEnd) {
                            // include both paid and partially paid in the selected month
                            return $a->status !== 'Unpaid'
                                && $a->updated_at->between($monthStart, $monthEnd);
                        })
                        ->map(function ($a, $index) {
                            return [
                                'term' => $index + 1,
                                'due_date' => $a->due_date->format('M d, Y'),
                                'date_paid' => $a->date_paid ? Carbon::parse($a->date_paid)->format('M d, Y') : null,
                                'status' => $a->status,
                                'installment' => number_format($a->installment, 2),
                                'amount_paid' => $a->amount_paid ? number_format($a->amount_paid, 2) : null,
                                'penalty' => $a->penalty_amount ? number_format($a->penalty_amount, 2) : '0.00',
                            ];
                        })
                        ->values();

                    // 🧮 Determine summary stats
                    $paidThisMonth = $payments->where('status', 'Paid')->count();
                    $partialThisMonth = $payments->where('status', 'Partial Paid')->count();

                    $coopSummary = [
                        'cooperative_name' => $coop->name,
                        'program_status' => $cp->program_status,
                        'has_amortization' => $hasAmortization,
                        'loan_amount' => $cp->loan_amount ? number_format($cp->loan_amount, 2) : '—',
                        'with_grace' => $cp->with_grace,
                        'payments' => $payments,
                        'stats' => [
                            'paid_this_month' => $paidThisMonth,
                            'partial_this_month' => $partialThisMonth,
                        ],
                    ];

                    if ($hasAmortization) {
                        $programSummary['has_amortization'][] = $coopSummary;
                    } else {
                        $programSummary['checklist_only'][] = [
                            'cooperative_name' => $coop->name,
                            'program_status' => $cp->program_status,
                            'has_checklist' => $cp->checklist()->exists(),
                        ];
                    }

                    $programSummary['cooperatives'][] = $coopSummary;
                }

                return $programSummary;
            });

        // 3️⃣ Generate PDF report
        $pdf = Pdf::loadView('monthly', [
            'date' => $selectedMonth->format('F Y'),
            'registeredCoops' => $registeredCoops,
            'programs' => $programs,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Monthly_Report_'.$selectedMonth->format('F_Y').'.pdf');
    }
}
