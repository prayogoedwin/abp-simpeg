<?php

namespace Database\Seeders;

use App\Models\JamKerja;
use App\Models\Instansi;
use App\Models\JenisPegawai;
use Illuminate\Database\Seeder;

class JamKerjaSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID Cleaning Service
        $cleaningService = JenisPegawai::where('nama', 'CLEANING SERVICE')->first();

        if (!$cleaningService) {
            $this->command->error('Jenis Pegawai CLEANING SERVICE tidak ditemukan. Jalankan JenisPegawaiSeeder dulu.');
            return;
        }

        // Data instansi yang perlu di-seed jam kerjanya
        $instansiNames = [
            'SEKRETARIAT DPRD',
            'BAPPERIDA',
            'SETDA',
            'BKPP',
            'INSPEKTORAT',
            'BPKAD',
            'DISTRAN',
            'DINSOS',
            'RSUD KARTINI JEPARA',
        ];

        foreach ($instansiNames as $nama) {
            $instansi = Instansi::where('nama', $nama)->first();

            if ($instansi) {
                JamKerja::updateOrCreate(
                    [
                        'instansi_id' => $instansi->id,
                        'jenis_pegawai_id' => $cleaningService->id,
                        'jenis_jam_kerja' => 'NORMAL',
                    ],
                    [
                        'jam_masuk' => '06:00',
                        'jam_pulang' => '16:00',
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('Seeded ' . count($instansiNames) . ' jam kerja records.');
    }
}