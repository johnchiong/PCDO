<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoopDetails extends Model
{
    protected $fillable = [
        'municipality',
        'asset_size',
        'coop_type',
        'status/category',
        'bond_of_membership',
        'area_of_operation',
        'citizenship',
        'members',
        'total_asset',
        'net_surplus',
    ];
}
