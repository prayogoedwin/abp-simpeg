<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistTemplateDetail extends Model
{
    protected $fillable = [
        'checklist_template_id',
        'type',
        'label',
        'value',
        'options', // for select, checkbox, radio
    ];

    public function checklistTemplate()
    {
        return $this->belongsTo(ChecklistTemplate::class);
    }
}
