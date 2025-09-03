<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoopUploads extends Model
{
    use HasFactory;
    protected $table = 'cooperative_uploads';
    protected $fillable = [
        'cooperative_id',
        'checklist_item_id',
        'file_name',
        'mime_type',
        'file_content'
    ];

    // Relationship to checklist item
    public function checklistItem()
    {
        return $this->belongsTo(Checklist::class, 'checklist_item_id');
    }

    // Relationship to cooperative
    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class, 'cooperative_id');
    }
}