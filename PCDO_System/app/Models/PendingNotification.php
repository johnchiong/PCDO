<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingNotification extends Model
{
    protected $fillable = [
        'schedule_id', 'coop_id', 'type', 'subject', 'body', 'processed'
    ];
    public $timestamps = false;


    public function pendingnotifications()
    {
        return $this->hasOne(AmortizationSchedules::class, 'schedule_id', 'id');
    }

    public function schedule()
    {
        return $this->belongsTo(AmortizationSchedules::class, 'schedule_id');
    }

    
}