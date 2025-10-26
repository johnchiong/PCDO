<?php

namespace App\Models;

use App\Traits\SyncLogger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoopMember extends Model
{
    /** @use HasFactory<\Database\Factories\CoopMemberFactory> */
    use HasFactory, SyncLogger;

    protected $fillable = [
        'coop_id',
        'name',
        'position',
        'biodata_path',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(CoopProgram::class, 'coop_program_id');
    }

    public function files()
    {
        return $this->hasMany(CoopMemberFile::class, 'coop_member_id');
    }
}
