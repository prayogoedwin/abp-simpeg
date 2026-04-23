<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JenisInstansiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Sekolah'
            ],
            [
                'nama' => 'Rumah Sakit'
            ],
            [
                'nama' => 'Perkantoran'
            ],
        ];

        foreach ($data as $jenis) {
            \App\Models\JenisInstansi::updateOrCreate(
                ['nama' => $jenis['nama']],
                $jenis
            );
        }
    }
}
