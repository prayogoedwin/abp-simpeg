<?php

namespace Database\Seeders;

use App\Models\JenisPegawai;
use Illuminate\Database\Seeder;

class JenisPegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'SATPAM'],
            ['nama' => 'CLEANING SERVICE'],
        ];

        foreach ($data as $item) {
            JenisPegawai::updateOrCreate(
                ['nama' => $item['nama']],
                $item
            );
        }

        $this->command->info('Seeded ' . count($data) . ' jenis pegawai records.');
    }
}