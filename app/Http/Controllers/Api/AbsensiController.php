<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JamKerja;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiController extends Controller
{

     private function isOvernightClockIn(Carbon $time): bool
    {
        $hour = $time->hour;
        return $hour >= 18 && $hour <= 23;
    }

    /**
     * Absen masuk
     */
    public function clockIn(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'foto' => 'nullable|image|max:2048',
        ]);

        $member = $request->user();
        $today = Carbon::today();

        // Cek ada absensi aktif yang belum clock out
        $activeAbsensi = Absensi::where('member_id', $member->id)
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->where('tanggal', '>=', Carbon::yesterday())
            ->first();

        if ($activeAbsensi) {
            return response()->json([
                'success' => false,
                'message' => 'Anda masih punya absensi aktif yang belum clock out.',
                'data' => [
                    'id' => $activeAbsensi->id,
                    'tanggal' => $activeAbsensi->tanggal->format('Y-m-d'),
                    'jam_masuk' => $activeAbsensi->jam_masuk_formatted,
                    'is_overnight' => $activeAbsensi->is_overnight,
                ],
            ], 422);
        }

        // Cek sudah absen masuk hari ini belum (untuk non-overnight)
        $existingAbsensi = Absensi::where('member_id', $member->id)
            ->whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->first();

        if ($existingAbsensi) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah absen masuk hari ini.',
                'data' => [
                    'id' => $existingAbsensi->id,
                    'tanggal' => $existingAbsensi->tanggal->format('Y-m-d'),
                    'jam_masuk' => $existingAbsensi->jam_masuk_formatted,
                ],
            ], 422);
        }

        // Get jadwal jam kerja
        $jamKerja = JamKerja::where('instansi_id', $member->instansi_id)
            ->where('jenis_pegawai_id', $member->jenis_pegawai_id ?? null)
            ->where('is_active', true)
            ->first();

        // Upload foto jika ada
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('absensi/masuk/' . $today->format('Y/m'), 'public');
        }

        // Hitung jarak dari instansi
        $jarak = null;
        if ($member->instansi && $member->instansi->lat && $member->instansi->lng) {
            $jarak = $member->instansi->distanceTo($request->lat, $request->lng);
            $jarak = $jarak ? $jarak * 1000 : null; // convert to meter
        }

        $jamMasuk = Carbon::now();
        $jadwalMasuk = $jamKerja?->jam_masuk;

        // Cek apakah overnight (clock in jam 18:00 - 23:59)
        $isOvernight = $this->isOvernightClockIn($jamMasuk);
        
        // Hitung telat
        $telatMenit = 0;
        $status = Absensi::STATUS_HADIR;
        
        if ($jadwalMasuk) {
            $jadwalMasukCarbon = Carbon::parse($jadwalMasuk);
            if ($jamMasuk->gt($jadwalMasukCarbon)) {
                $telatMenit = $jamMasuk->diffInMinutes($jadwalMasukCarbon);
                $status = Absensi::STATUS_TERLAMBAT;
            }
        }

        // Create atau update absensi
        $absensi = Absensi::updateOrCreate(
            [
                'member_id' => $member->id,
                'tanggal' => $today,
            ],
            [
                'instansi_id' => $member->instansi_id,
                // 'jadwal_jam_masuk' => $jadwalMasuk,
                // 'jadwal_jam_pulang' => $jamKerja?->jam_pulang,
                'jam_masuk' => $jamMasuk->format('H:i:s'),
                'status' => $status,
                'is_overnight' => $isOvernight,
                'telat_menit' => $telatMenit,
                'lat_masuk' => $request->lat,
                'lng_masuk' => $request->lng,
                'jarak_lokasi_masuk' => $jarak,
                'foto_masuk' => $fotoPath,
                'device_info' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => $status === Absensi::STATUS_TERLAMBAT 
                ? "Absen masuk berhasil. Anda terlambat {$telatMenit} menit."
                : 'Absen masuk berhasil.',
            'data' => [
                'id' => $absensi->id,
                'tanggal' => $absensi->tanggal->format('Y-m-d'),
                'jam_masuk' => $absensi->jam_masuk_formatted,
                'status' => $absensi->status,
                'status_label' => $absensi->status_label,
                'telat_menit' => $absensi->telat_menit,
                'jarak_masuk' => $absensi->jarak_masuk_formatted,
            ],
        ]);
    }

    /**
     * Absen pulang
     */
    public function clockOut(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'foto' => 'nullable|image|max:2048',
        ]);

        $member = $request->user();
        $now = Carbon::now();
        $today = Carbon::today();

        // Cari absensi aktif (belum clock out)
        // Untuk shift malam, cek juga kemarin
        $absensi = Absensi::where('member_id', $member->id)
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->where(function ($query) use ($today) {
                $query->whereDate('tanggal', $today) // Absensi hari ini
                    ->orWhere(function ($q) use ($today) {
                        // Atau absensi kemarin yang overnight
                        $q->whereDate('tanggal', Carbon::yesterday())
                            ->where('is_overnight', true);
                    });
            })
            ->orderBy('tanggal', 'desc')
            ->first();

        if (!$absensi) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada absensi aktif. Silakan clock in terlebih dahulu.',
            ], 422);
        }

        // Upload foto jika ada
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('absensi/pulang/' . $today->format('Y/m'), 'public');
        }

        // Hitung jarak dari instansi
        $jarak = null;
        if ($member->instansi && $member->instansi->lat && $member->instansi->lng) {
            $jarak = $member->instansi->distanceTo($request->lat, $request->lng);
            $jarak = $jarak ? $jarak * 1000 : null; // convert to meter
        }

        $jamPulang = $now;
        $jadwalPulang = $absensi->jadwal_jam_pulang;

        // Hitung pulang awal
        $pulangAwalMenit = 0;
        if ($jadwalPulang) {
            $jadwalPulangCarbon = Carbon::parse($jadwalPulang);
            
            // Untuk overnight, jadwal pulang adalah hari berikutnya
            if ($absensi->is_overnight) {
                $jadwalPulangCarbon->setDate(
                    $absensi->tanggal->copy()->addDay()->year,
                    $absensi->tanggal->copy()->addDay()->month,
                    $absensi->tanggal->copy()->addDay()->day
                );
            } else {
                $jadwalPulangCarbon->setDate(
                    $absensi->tanggal->year,
                    $absensi->tanggal->month,
                    $absensi->tanggal->day
                );
            }

            if ($jamPulang->lt($jadwalPulangCarbon)) {
                $pulangAwalMenit = $jadwalPulangCarbon->diffInMinutes($jamPulang);
            }
        }

        // Update absensi
        $absensi->update([
            'jam_pulang' => $jamPulang->format('H:i:s'),
            'pulang_awal_menit' => $pulangAwalMenit,
            'lat_pulang' => $request->lat,
            'lng_pulang' => $request->lng,
            'jarak_lokasi_pulang' => $jarak,
            'foto_pulang' => $fotoPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => $pulangAwalMenit > 0 
                ? "Absen pulang berhasil. Anda pulang {$pulangAwalMenit} menit lebih awal."
                : 'Absen pulang berhasil.',
            'data' => [
                'id' => $absensi->id,
                'tanggal' => $absensi->tanggal->format('Y-m-d'),
                'jam_masuk' => $absensi->jam_masuk_formatted,
                'jam_pulang' => $absensi->jam_pulang_formatted,
                'status' => $absensi->status,
                'status_label' => $absensi->status_label,
                'pulang_awal_menit' => $absensi->pulang_awal_menit,
                'jarak_pulang' => $absensi->jarak_pulang_formatted,
            ],
        ]);
    }

    /**
     * Get status absensi hari ini
     */
    public function today(Request $request)
    {
        $member = $request->user();
        $today = Carbon::today();

        $absensi = Absensi::where('member_id', $member->id)
            ->whereDate('tanggal', $today)
            ->whereNull('deleted_at')
            ->first();

        // Get jadwal jam kerja
        $jamKerja = JamKerja::where('instansi_id', $member->instansi_id)
            ->where('jenis_pegawai_id', $member->jenis_pegawai_id ?? null)
            ->where('is_active', true)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'tanggal' => $today->format('Y-m-d'),
                'hari' => $today->translatedFormat('l'),
                // 'jadwal' => [
                //     'jam_masuk' => $jamKerja?->jam_masuk?->format('H:i'),
                //     'jam_pulang' => $jamKerja?->jam_pulang?->format('H:i'),
                //     'jenis' => $jamKerja?->jenis_jam_kerja,
                // ],
                'absensi' => $absensi ? [
                    'id' => $absensi->id,
                    'jam_masuk' => $absensi->jam_masuk_formatted,
                    'jam_pulang' => $absensi->jam_pulang_formatted,
                    'status' => $absensi->status,
                    'status_label' => $absensi->status_label,
                    'telat_menit' => $absensi->telat_menit,
                    'pulang_awal_menit' => $absensi->pulang_awal_menit,
                    'jarak_masuk' => $absensi->jarak_masuk_formatted,
                    'jarak_pulang' => $absensi->jarak_pulang_formatted,
                    'sudah_masuk' => $absensi->jam_masuk !== null,
                    'sudah_pulang' => $absensi->jam_pulang !== null,
                ] : [
                    'sudah_masuk' => false,
                    'sudah_pulang' => false,
                ],
                'instansi' => $member->instansi?->only(['id', 'nama', 'lat', 'lng', 'radius']),
            ],
        ]);
    }

    public function last(Request $request)
    {
        $member = $request->user();

        // Cari absensi yang belum pulang (untuk shift malam)
        $absensi = Absensi::where('member_id', $member->id)
            ->whereNull('deleted_at')
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_pulang')
            ->orderBy('tanggal', 'desc')
            ->first();

        // Jika tidak ada yang belum pulang, ambil absensi terakhir
        if (!$absensi) {
            $absensi = Absensi::where('member_id', $member->id)
                ->whereNull('deleted_at')
                ->orderBy('tanggal', 'desc')
                ->first();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'absensi' => $absensi ? [
                    'id' => $absensi->id,
                    'tanggal' => $absensi->tanggal->format('Y-m-d'),
                    'hari' => $absensi->tanggal->translatedFormat('l'),
                    'jam_masuk' => $absensi->jam_masuk_formatted,
                    'jam_pulang' => $absensi->jam_pulang_formatted,
                    'status' => $absensi->status,
                    'status_label' => $absensi->status_label,
                    'telat_menit' => $absensi->telat_menit,
                    'pulang_awal_menit' => $absensi->pulang_awal_menit,
                    'jarak_masuk' => $absensi->jarak_masuk_formatted,
                    'jarak_pulang' => $absensi->jarak_pulang_formatted,
                    'sudah_masuk' => $absensi->jam_masuk !== null,
                    'sudah_pulang' => $absensi->jam_pulang !== null,
                    'is_pending_checkout' => $absensi->jam_masuk !== null && $absensi->jam_pulang === null,
                ] : null,
                'instansi' => $member->instansi?->only(['id', 'nama', 'lat', 'lng', 'radius']),
            ],
        ]);
    }

    /**
     * Get rekap absensi bulanan
     */
    public function rekap(Request $request)
    {
        $request->validate([
            'bulan' => 'nullable|integer|min:1|max:12',
            'tahun' => 'nullable|integer|min:2020|max:2100',
        ]);

        $member = $request->user();
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        $rekap = Absensi::getRekapBulanan($member->id, $bulan, $tahun);
        $summary = Absensi::getSummaryBulanan($member->id, $bulan, $tahun);

        // Format rekap untuk API
        $rekapFormatted = collect($rekap)->map(fn ($row) => [
            'tanggal' => $row['tanggal']->format('Y-m-d'),
            'hari' => $row['hari'],
            'jam_masuk' => $row['jam_masuk'],
            'jam_pulang' => $row['jam_pulang'],
            'status' => $row['status_raw'],
            'status_label' => $row['status'],
            'keterangan' => $row['keterangan'] === '-' ? null : $row['keterangan'],
            'telat_menit' => $row['telat_menit'],
            'jarak_masuk' => $row['jarak_masuk'] === '-' ? null : $row['jarak_masuk'],
            'jarak_pulang' => $row['jarak_pulang'] === '-' ? null : $row['jarak_pulang'],
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'periode' => [
                    'bulan' => (int) $bulan,
                    'bulan_nama' => Carbon::create($tahun, $bulan, 1)->translatedFormat('F'),
                    'tahun' => (int) $tahun,
                ],
                'summary' => $summary,
                'rekap' => $rekapFormatted,
            ],
        ]);
    }

    /**
     * Get history absensi (pagination)
     */
    public function history(Request $request)
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:5|max:50',
        ]);

        $member = $request->user();
        $perPage = $request->per_page ?? 10;

        $absensis = Absensi::where('member_id', $member->id)
            ->orderBy('tanggal', 'desc')
            ->paginate($perPage);

        $absensis->getCollection()->transform(fn ($item) => [
            'id' => $item->id,
            'tanggal' => $item->tanggal->format('Y-m-d'),
            'hari' => $item->tanggal->translatedFormat('l'),
            'jam_masuk' => $item->jam_masuk_formatted,
            'jam_pulang' => $item->jam_pulang_formatted,
            'status' => $item->status,
            'status_label' => $item->status_label,
            'telat_menit' => $item->telat_menit,
            'keterangan' => $item->keterangan,
        ]);

        return response()->json([
            'success' => true,
            'data' => $absensis,
        ]);
    }

    /**
     * Submit izin/sakit/cuti
     * POST /api/absensi/izin
     */
    public function storeStatusLainnya(Request $request)
    {
        $request->validate([
            'status' => 'required|in:izin,sakit,cuti',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $member = $request->user();
        $tanggal = Carbon::parse($request->tanggal);

        // Cek apakah sudah ada absensi di tanggal tersebut
        $existing = Absensi::where('member_id', $member->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Sudah ada data absensi di tanggal tersebut.',
                'data' => [
                    'id' => $existing->id,
                    'tanggal' => $existing->tanggal->format('Y-m-d'),
                    'status' => $existing->status,
                ],
            ], 422);
        }

        // Create absensi dengan status izin/sakit/cuti
        $absensi = Absensi::create([
            'member_id' => $member->id,
            'instansi_id' => $member->instansi_id,
            'tanggal' => $tanggal,
            'jam_masuk' => null,
            'jam_pulang' => null,
            'status' => $request->status,
            'keterangan' => $request->keterangan ?? ucfirst($request->status),
            'device_info' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan ' . $request->status . ' berhasil disimpan.',
            'data' => [
                'id' => $absensi->id,
                'tanggal' => $absensi->tanggal->format('Y-m-d'),
                'hari' => $absensi->tanggal->translatedFormat('l'),
                'status' => $absensi->status,
                'status_label' => $absensi->status_label,
                'keterangan' => $absensi->keterangan,
            ],
        ]);
    }
}