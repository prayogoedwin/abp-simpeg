<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Laravel\Sanctum\HasApiTokens;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Member extends Model implements Authenticatable
{
    use HasFactory, SoftDeletes, AuthenticatableTrait, CanResetPasswordTrait, LogsActivity, HasApiTokens;

    protected $table = 'members';

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'whatsapp',
        'alamat',
        'provider',
        'provider_id',
        // Kolom outsourcing baru
        'instansi_id',
        'posisi_id',
        'nik',
        'no_karyawan',
        'tanggal_lahir',
        'jenis_kelamin',
        'tanggal_masuk',
        'tanggal_kontrak_berakhir',
        'status_kepegawaian',
        'foto',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tanggal_lahir' => 'date',
            'tanggal_masuk' => 'date',
            'tanggal_kontrak_berakhir' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Pegawai {$eventName}");
    }

    // ==================== AUTH METHODS ====================
    
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getRememberToken()
    {
        return $this->remember_token;
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    // ==================== RELATIONSHIPS ====================

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class, 'instansi_id');
    }

    public function posisi(): BelongsTo
    {
        return $this->belongsTo(Posisi::class, 'posisi_id');
    }

    // ==================== SCOPES ====================

    public function scopeAktif($query)
    {
        return $query->where('status_kepegawaian', 'aktif');
    }

    public function scopeByInstansi($query, $instansiId)
    {
        return $query->where('instansi_id', $instansiId);
    }

    public function scopeByPosisi($query, $posisiId)
    {
        return $query->where('posisi_id', $posisiId);
    }

    public function scopeKontrakHampirBerakhir($query, $days = 30)
    {
        return $query->whereNotNull('tanggal_kontrak_berakhir')
            ->whereBetween('tanggal_kontrak_berakhir', [now(), now()->addDays($days)]);
    }

    // ==================== ACCESSORS ====================

    public function getUmurAttribute(): ?int
    {
        return $this->tanggal_lahir ? $this->tanggal_lahir->age : null;
    }

    public function getMasaKerjaAttribute(): ?string
    {
        if (!$this->tanggal_masuk) return null;
        
        $diff = $this->tanggal_masuk->diff(now());
        return "{$diff->y} tahun {$diff->m} bulan";
    }

    public function getJenisKelaminLabelAttribute(): ?string
    {
        return match($this->jenis_kelamin) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => null,
        };
    }

    public function getSisaKontrakAttribute(): ?int
    {
        if (!$this->tanggal_kontrak_berakhir) return null;
        return now()->diffInDays($this->tanggal_kontrak_berakhir, false);
    }

    // ==================== HELPERS ====================

    public function isKontrakAkanBerakhir(int $days = 30): bool
    {
        if (!$this->tanggal_kontrak_berakhir) return false;
        return $this->sisa_kontrak <= $days && $this->sisa_kontrak >= 0;
    }

    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'member_id');
    }
}