<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Instansi;
use App\Models\Posisi;

class OutsourcingSeeder extends Seeder
{
    public function run(): void
    {
        // Seed posisi umum
        $posisis = [
            ['nama' => 'Satpam', 'kode' => 'SATPAM'],
            ['nama' => 'Cleaning Service', 'kode' => 'CS'],
            ['nama' => 'Driver', 'kode' => 'DRIVER'],
            ['nama' => 'Resepsionis', 'kode' => 'RESEPSIONIS'],
            ['nama' => 'Office Boy', 'kode' => 'OB'],
            ['nama' => 'Teknisi', 'kode' => 'TEKNISI'],
        ];

        foreach ($posisis as $posisi) {
            Posisi::firstOrCreate(['kode' => $posisi['kode']], $posisi);
        }
    }
}