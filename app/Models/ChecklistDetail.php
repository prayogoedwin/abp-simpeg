<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistDetail extends Model
{
    protected $fillable = [
        'checklist_id',
        'type',
        'label',
        'value',
        'options', // for select, checkbox, radio
    ];

    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }
}
