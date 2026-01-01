<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JamKerja extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jam_kerjas';

    protected $fillable = [
        'instansi_id',
        'jenis_pegawai_id',
        'jenis_jam_kerja',
        'jam_masuk',
        'jam_pulang',
        'is_active',
    ];

    protected $casts = [
        'jam_masuk' => 'datetime:H:i',
        'jam_pulang' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    // ==================== CONSTANTS ====================

    const JENIS_JAM_KERJA = [
        'NORMAL' => 'Normal',
        'SHIFT 1' => 'Shift 1',
        'SHIFT 2' => 'Shift 2',
        'SHIFT 3' => 'Shift 3',
        'LONGSHIFT' => 'Long Shift',
    ];

    // ==================== RELATIONSHIPS ====================

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class, 'instansi_id');
    }

    public function jenisPegawai(): BelongsTo
    {
        return $this->belongsTo(JenisPegawai::class, 'jenis_pegawai_id');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByInstansi($query, $instansiId)
    {
        return $query->where('instansi_id', $instansiId);
    }

    public function scopeByJenisPegawai($query, $jenisPegawaiId)
    {
        return $query->where('jenis_pegawai_id', $jenisPegawaiId);
    }

    // ==================== ACCESSORS ====================

    public function getJamMasukFormattedAttribute(): string
    {
        return $this->jam_masuk ? $this->jam_masuk->format('H:i') : '-';
    }

    public function getJamPulangFormattedAttribute(): string
    {
        return $this->jam_pulang ? $this->jam_pulang->format('H:i') : '-';
    }

    public function getDurasiKerjaAttribute(): ?string
    {
        if ($this->jam_masuk && $this->jam_pulang) {
            $diff = $this->jam_masuk->diff($this->jam_pulang);
            return $diff->format('%H:%I');
        }
        return null;
    }
}