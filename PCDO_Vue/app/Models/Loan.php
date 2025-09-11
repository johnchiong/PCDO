<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = [
        'cooperative_id',
        'program_id',
        'amount',
        'start_date',
        'grace_period',
        'term_months',
    ];

    public function schedules()
    {
        return $this->hasMany(AmmortizationSchedule::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class, 'cooperative_id');
    }

    public function generateSchedule()
    {
        $monthsToPay = $this->term_months - $this->grace_period;

        if ($monthsToPay <= 0) {
            throw new \Exception('Invalid term and grace period.');
        }

        $amountPerMonth = round($this->amount / $monthsToPay, 2);
        $startDate = Carbon::parse($this->start_date)->addMonths($this->grace_period);

        for ($i = 1; $i <= $monthsToPay; $i++) {
            $amountDue = ($i === $monthsToPay)
                ? $this->amount - ($amountPerMonth * ($monthsToPay - 1))
                : $amountPerMonth;

            $this->schedules()->create([
                'due_date' => $startDate->copy()->addMonths($i - 1),
                'amount_due' => $amountDue,
                'penalty_amount' => 0,
                'is_paid' => false,
            ]);
        }
    }
}
