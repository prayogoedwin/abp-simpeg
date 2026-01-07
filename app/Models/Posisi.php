<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Posisi extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'posisis';

    protected $fillable = [
        'nama',
        'kode',
        'deskripsi',
        'gaji_pokok',
        'status',
    ];

    protected $casts = [
        'gaji_pokok' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Posisi {$eventName}");
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'posisi_id');
    }

    public function pegawais(): HasMany
    {
        return $this->members();
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    // Format gaji untuk display
    public function getGajiFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->gaji_pokok, 0, ',', '.');
    }
}