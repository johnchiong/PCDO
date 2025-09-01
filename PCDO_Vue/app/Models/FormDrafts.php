<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormDrafts extends Model
{
    protected $fillable = [
        'user_id', 
        'entity_type', 
        'entity_id', 
        'form_name', 
        'data', 
        'last_editor_id', 
        'status', 
        'version', 
        'locked_by', 
        'locked_at', 
        'notes'
    ];

    protected $casts = [
        'data' => 'array',
        'locked_at' => 'datetime',
    ];
}
