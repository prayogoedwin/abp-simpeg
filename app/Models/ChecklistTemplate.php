<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistTemplate extends Model
{
    protected $fillable = [
        'instansi_id',
        'name'
    ];


    public function instansi()
    {
        return $this->belongsTo(Instansi::class);
    }

    public function details()
    {
        return $this->hasMany(ChecklistTemplateDetail::class);
    }
}
