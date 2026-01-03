<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Instansi extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'instansis';

    protected $fillable = [
        'nama',
        'kode',
        'alamat',
        'lat',
        'lng',
        'radius',
        'google_maps_link',
        'telepon',
        'email',
        'pic_name',
        'pic_phone',
        'status',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Instansi {$eventName}");
    }

    // ==================== RELATIONSHIPS ====================

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'instansi_id');
    }

    public function pegawais(): HasMany
    {
        return $this->members();
    }

    // ==================== SCOPES ====================

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeHasLocation($query)
    {
        return $query->whereNotNull('lat')->whereNotNull('lng');
    }

    // ==================== ACCESSORS ====================

    /**
     * Get koordinat sebagai array
     */
    public function getKoordinatAttribute(): ?array
    {
        if ($this->lat && $this->lng) {
            return [
                'lat' => (float) $this->lat,
                'lng' => (float) $this->lng,
            ];
        }
        return null;
    }

    /**
     * Generate Google Maps link otomatis jika belum ada
     */
    public function getGoogleMapsUrlAttribute(): ?string
    {
        if ($this->google_maps_link) {
            return $this->google_maps_link;
        }

        if ($this->lat && $this->lng) {
            return "https://www.google.com/maps?q={$this->lat},{$this->lng}";
        }

        return null;
    }

    /**
     * Generate embed URL untuk iframe Google Maps
     */
    public function getGoogleMapsEmbedUrlAttribute(): ?string
    {
        if ($this->lat && $this->lng) {
            return "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3000!2d{$this->lng}!3d{$this->lat}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zM!5e0!3m2!1sen!2sid!4v1600000000000!5m2!1sen!2sid";
        }
        return null;
    }

    // ==================== HELPERS ====================

    /**
     * Set koordinat dari Google Maps link
     */
    public function extractCoordsFromGoogleLink(): bool
    {
        if (!$this->google_maps_link) return false;

        // Pattern untuk extract lat,lng dari berbagai format Google Maps URL
        $patterns = [
            '/@(-?\d+\.\d+),(-?\d+\.\d+)/',      // Format: @-6.123456,106.123456
            '/q=(-?\d+\.\d+),(-?\d+\.\d+)/',     // Format: ?q=-6.123456,106.123456
            '/place\/(-?\d+\.\d+),(-?\d+\.\d+)/', // Format: /place/-6.123456,106.123456
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $this->google_maps_link, $matches)) {
                $this->lat = $matches[1];
                $this->lng = $matches[2];
                return true;
            }
        }

        return false;
    }

    /**
     * Hitung jarak ke koordinat lain (dalam km)
     */
    public function distanceTo(float $lat, float $lng): ?float
    {
        if (!$this->lat || !$this->lng) return null;

        $earthRadius = 6371; // km

        $latFrom = deg2rad($this->lat);
        $lngFrom = deg2rad($this->lng);
        $latTo = deg2rad($lat);
        $lngTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $a = sin($latDelta / 2) ** 2 +
             cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}