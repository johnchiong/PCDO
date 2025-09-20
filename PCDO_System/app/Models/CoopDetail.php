<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoopDetail extends Model
{
    protected $fillable = [
        'coop_id',
        'region_id',
        'province_id',
        'municipality_id',
        'barangay_id',
        'asset_size',
        'coop_type',
        'status/category',
        'bond_of_membership',
        'area_of_operation',
        'citizenship',
        'members_count',
        'total_asset',
        'net_surplus',
    ];

    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class, 'coop_id');
    }
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
    public function province()
    {
        return $this->belongsTo(Province::class);
    }
    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }
    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }
}
