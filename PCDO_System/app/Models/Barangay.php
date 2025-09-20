<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    protected $fillable = [
        'name',
        'municipalities_id',
    ];
    public $timestamps = false;

    public function barangay()
    {
        return $this->hasMany(Barangay::class, 'barangay_id');
    }
}
