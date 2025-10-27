<?php

namespace App\Models;

use App\Traits\SyncLogger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    /** @use HasFactory<\Database\Factories\ReportFactory>
     *
     */
    use HasFactory, SyncLogger;
}
