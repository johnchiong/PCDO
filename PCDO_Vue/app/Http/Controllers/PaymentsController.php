<?php
namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\AmmortizationSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentsController extends Controller
{
    public function index()
    {
        $cooperatives = \App\Models\Cooperative::with([
            'loan.program',
            'loan.schedules'
        ])->get();

        return \Inertia\Inertia::render('payments/index', [
            'cooperatives' => $cooperatives,
        ]);
    }

    public function amortization($loanId)
    {
        $loan = Loan::with([
            'cooperative',
            'program',
            'schedules' => function ($query) {
                $query->orderBy('due_date');
            },
        ])->findOrFail($loanId);

        return Inertia::render('payments/amortization', ['loan' => $loan]);
    }

    public function penalty(Request $request, $scheduleId)
    {
        $schedule = AmmortizationSchedule::findOrFail($scheduleId);

        if ($schedule->is_paid) {
            return back()->with('error', 'Cannot change penalty for a paid schedule.');
        }

        if ($schedule->penalty_amount > 0) {
            // Remove penalty
            $schedule->penalty_amount = 0;
        } else {
            // Add penalty = 1% of amount_due
            $schedule->penalty_amount = $schedule->amount_due * 0.01;
        }

        $schedule->save();
        return back()->with('success', 'Penalty updated.');
    }

    public function markPaid($scheduleId)
    {
        $schedule = AmmortizationSchedule::findOrFail($scheduleId);
        $schedule->markPaid();
        return back()->with('success', 'Payment marked as paid.');
    }
    public function notePayment(Request $request, $id)
    {
        $schedule = AmmortizationSchedule::findOrFail($id);

        $request->validate([
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $payment = $request->amount_paid;
        $remaining = $payment;

        $schedules = AmmortizationSchedule::where('loan_id', $schedule->loan_id)
            ->orderBy('due_date')
            ->get();

        foreach ($schedules as $index => $sch) {
            if ($remaining <= 0)
                break;

            $due = $sch->amount_due + $sch->penalty_amount;
            $alreadyPaid = $sch->amount_paid;
            $needed = $due - $alreadyPaid;

            if ($needed <= 0) {
                $sch->is_paid = true;
                $sch->balance = 0;
                $sch->paid_at = $sch->paid_at ?? now();
            } else {
                $toPay = min($remaining, $needed);
                $sch->amount_paid += $toPay;
                $sch->balance = $due - $sch->amount_paid;
                $remaining -= $toPay;

                if ($sch->balance <= 0) {
                    // Fully paid
                    $sch->is_paid = true;
                    $sch->balance = 0;
                    $sch->paid_at = now();
                } else {
                    // Partially paid
                    $sch->is_paid = false;
                    $sch->paid_at = now();
                }
            }

            $sch->save();
        }

        return back()->with('success', 'Payment noted successfully.');
    }
    public function sendNotification($loanId)
    {
        return back()->with('success', "Notification sent for Loan #{$loanId}");
    }
}
