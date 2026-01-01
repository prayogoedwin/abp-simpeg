<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Absensi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'absensis';

    protected $fillable = [
        'member_id',
        'instansi_id',
        'tanggal',
        'jadwal_jam_masuk',
        'jadwal_jam_pulang',
        'jam_masuk',
        'jam_pulang',
        'status',
        'is_overnight',
        'telat_menit',
        'pulang_awal_menit',
        'lat_masuk',
        'lng_masuk',
        'jarak_lokasi_masuk',
        'lat_pulang',
        'lng_pulang',
        'jarak_lokasi_pulang',
        'foto_masuk',
        'foto_pulang',
        'device_info',
        'keterangan',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jadwal_jam_masuk' => 'datetime:H:i',
        'jadwal_jam_pulang' => 'datetime:H:i',
        'jam_masuk' => 'datetime:H:i',
        'jam_pulang' => 'datetime:H:i',
        'lat_masuk' => 'decimal:8',
        'lng_masuk' => 'decimal:8',
        'lat_pulang' => 'decimal:8',
        'lng_pulang' => 'decimal:8',
        'jarak_lokasi_masuk' => 'decimal:2',
        'jarak_lokasi_pulang' => 'decimal:2',
        'device_info' => 'array',
        'approved_at' => 'datetime',
        'is_overnight' => 'boolean',
    ];

    // ==================== CONSTANTS ====================

    const STATUS_HADIR = 'hadir';
    const STATUS_ALPHA = 'alpha';
    const STATUS_IZIN = 'izin';
    const STATUS_SAKIT = 'sakit';
    const STATUS_CUTI = 'cuti';
    const STATUS_TERLAMBAT = 'terlambat';
    const STATUS_LIBUR = 'libur';

    const STATUS_OPTIONS = [
        self::STATUS_HADIR => 'Hadir',
        self::STATUS_ALPHA => 'Alpha',
        self::STATUS_IZIN => 'Izin',
        self::STATUS_SAKIT => 'Sakit',
        self::STATUS_CUTI => 'Cuti',
        self::STATUS_TERLAMBAT => 'Terlambat',
        self::STATUS_LIBUR => 'Libur',
    ];

    const STATUS_COLORS = [
        self::STATUS_HADIR => 'success',
        self::STATUS_ALPHA => 'danger',
        self::STATUS_IZIN => 'warning',
        self::STATUS_SAKIT => 'info',
        self::STATUS_CUTI => 'gray',
        self::STATUS_TERLAMBAT => 'warning',
        self::STATUS_LIBUR => 'gray',
    ];

    // ==================== RELATIONSHIPS ====================

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class, 'instansi_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ==================== SCOPES ====================

    public function scopeByMember($query, $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    public function scopeByInstansi($query, $instansiId)
    {
        return $query->where('instansi_id', $instansiId);
    }

    public function scopeByTanggal($query, $tanggal)
    {
        return $query->whereDate('tanggal', $tanggal);
    }

    public function scopeByBulan($query, $bulan, $tahun)
    {
        return $query->whereMonth('tanggal', $bulan)
                     ->whereYear('tanggal', $tahun);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // ==================== ACCESSORS ====================

    public function getJamMasukFormattedAttribute(): string
    {
        return $this->jam_masuk ? Carbon::parse($this->jam_masuk)->format('H:i') : '-';
    }

    public function getJamPulangFormattedAttribute(): string
    {
        return $this->jam_pulang ? Carbon::parse($this->jam_pulang)->format('H:i') : '-';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_OPTIONS[$this->status] ?? '-';
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function getJarakMasukFormattedAttribute(): string
    {
        if ($this->jarak_lokasi_masuk === null) return '-';
        
        if ($this->jarak_lokasi_masuk >= 1000) {
            return number_format($this->jarak_lokasi_masuk / 1000, 2) . ' km';
        }
        return number_format($this->jarak_lokasi_masuk, 0) . ' m';
    }

    public function getJarakPulangFormattedAttribute(): string
    {
        if ($this->jarak_lokasi_pulang === null) return '-';
        
        if ($this->jarak_lokasi_pulang >= 1000) {
            return number_format($this->jarak_lokasi_pulang / 1000, 2) . ' km';
        }
        return number_format($this->jarak_lokasi_pulang, 0) . ' m';
    }

    // ==================== HELPERS ====================

    /**
     * Hitung jarak dari koordinat ke lokasi instansi
     */
    public function hitungJarakMasuk(): ?float
    {
        if (!$this->lat_masuk || !$this->lng_masuk || !$this->instansi) {
            return null;
        }

        $jarak = $this->instansi->distanceTo($this->lat_masuk, $this->lng_masuk);
        return $jarak ? $jarak * 1000 : null; // convert km to meter
    }

    public function hitungJarakPulang(): ?float
    {
        if (!$this->lat_pulang || !$this->lng_pulang || !$this->instansi) {
            return null;
        }

        $jarak = $this->instansi->distanceTo($this->lat_pulang, $this->lng_pulang);
        return $jarak ? $jarak * 1000 : null; // convert km to meter
    }

    /**
     * Hitung keterlambatan dalam menit
     */
    public function hitungTelat(): ?int
    {
        if (!$this->jam_masuk || !$this->jadwal_jam_masuk) {
            return null;
        }

        $jadwal = Carbon::parse($this->jadwal_jam_masuk);
        $aktual = Carbon::parse($this->jam_masuk);

        if ($aktual->gt($jadwal)) {
            return $aktual->diffInMinutes($jadwal);
        }

        return 0;
    }

    /**
     * Hitung pulang awal dalam menit
     */
    public function hitungPulangAwal(): ?int
    {
        if (!$this->jam_pulang || !$this->jadwal_jam_pulang) {
            return null;
        }

        $jadwal = Carbon::parse($this->jadwal_jam_pulang);
        $aktual = Carbon::parse($this->jam_pulang);

        if ($aktual->lt($jadwal)) {
            return $jadwal->diffInMinutes($aktual);
        }

        return 0;
    }

    /**
     * Auto-compute sebelum save
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto hitung jarak
            if ($model->lat_masuk && $model->lng_masuk) {
                $model->jarak_lokasi_masuk = $model->hitungJarakMasuk();
            }
            if ($model->lat_pulang && $model->lng_pulang) {
                $model->jarak_lokasi_pulang = $model->hitungJarakPulang();
            }

            // Auto hitung telat & pulang awal
            $model->telat_menit = $model->hitungTelat();
            $model->pulang_awal_menit = $model->hitungPulangAwal();
        });
    }

    /**
     * Get rekap bulanan untuk member
     */
    public static function getRekapBulanan(int $memberId, int $bulan, int $tahun): array
    {
        $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $absensis = self::where('member_id', $memberId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->keyBy(fn ($item) => $item->tanggal->format('Y-m-d'));

        $rekap = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $absensi = $absensis->get($dateKey);

            $rekap[] = [
                'tanggal' => $currentDate->copy(),
                'hari' => $currentDate->translatedFormat('l'),
                'jam_masuk' => $absensi?->jam_masuk_formatted ?? '-',
                'jam_pulang' => $absensi?->jam_pulang_formatted ?? '-',
                'status' => $absensi?->status_label ?? '-',
                'status_raw' => $absensi?->status,
                'keterangan' => $absensi?->keterangan ?? '-',
                'telat_menit' => $absensi?->telat_menit,
                'jarak_masuk' => $absensi?->jarak_masuk_formatted ?? '-',
                'jarak_pulang' => $absensi?->jarak_pulang_formatted ?? '-',
            ];

            $currentDate->addDay();
        }

        return $rekap;
    }

    /**
     * Get summary statistik bulanan
     */
    public static function getSummaryBulanan(int $memberId, int $bulan, int $tahun): array
    {
        $absensis = self::where('member_id', $memberId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        return [
            'total_hadir' => $absensis->where('status', self::STATUS_HADIR)->count(),
            'total_terlambat' => $absensis->where('status', self::STATUS_TERLAMBAT)->count(),
            'total_alpha' => $absensis->where('status', self::STATUS_ALPHA)->count(),
            'total_izin' => $absensis->where('status', self::STATUS_IZIN)->count(),
            'total_sakit' => $absensis->where('status', self::STATUS_SAKIT)->count(),
            'total_cuti' => $absensis->where('status', self::STATUS_CUTI)->count(),
            'total_libur' => $absensis->where('status', self::STATUS_LIBUR)->count(),
            'total_menit_telat' => $absensis->sum('telat_menit'),
            'total_menit_pulang_awal' => $absensis->sum('pulang_awal_menit'),
        ];
    }
}