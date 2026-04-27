<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    protected $fillable = [
        'instansi_id',
        'member_id',
        'checklist_template_id',
    ];

    public function details()
    {
        return $this->hasMany(ChecklistDetail::class);
    }
}
