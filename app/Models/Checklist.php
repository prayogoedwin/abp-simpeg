<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    protected $fillable = [
        'instansi_id',
    ];

    public function checklistdetails()
    {
        return $this->hasMany(ChecklistDetail::class);
    }
}
