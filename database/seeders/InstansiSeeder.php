<?php

namespace Database\Seeders;

use App\Models\Instansi;
use Illuminate\Database\Seeder;

class InstansiSeeder extends Seeder
{
    public function run(): void
    {
        $instansis = [
            [
                'nama' => 'SEKRETARIAT DPRD PATI',
                'kode' => 'SEKRETARIAT-DPRD-PATI',
                'alamat' => 'Jl. DR. Wahidin No.2A, Kaborongan, Pati Lor, Kec. Pati, Kabupaten Pati',
                'lat' => -6.7525264,
                'lng' => 111.0383086,
                'status' => 'aktif',
            ],
            [
                'nama' => 'BAPPERIDA PATI',
                'kode' => 'BAPPERIDA-PATI',
                'alamat' => 'Jl. Kudus - Pati No.KM.4, Sawah, Margorejo, Kec. Pati, Kabupaten Pati',
                'lat' => -6.7832521,
                'lng' => 110.8672131,
                'status' => 'aktif',
            ],
            [
                'nama' => 'SETDA PATI',
                'kode' => 'SETDA-PATI',
                'alamat' => 'Jl. Tombronegoro No.1, Kaborongan, Pati Lor, Kec. Pati',
                'lat' => -6.7523826,
                'lng' => 111.0374129,
                'status' => 'aktif',
            ],
            [
                'nama' => 'BKPP PATI',
                'kode' => 'BKPP-PATI',
                'alamat' => 'Jl. Kudus - Pati No.KM.4, Sawah, Margorejo, Kec. Pati, Kabupaten Pati, Jawa Tengah 59163',
                'lat' => -6.7624626,
                'lng' => 111.0110569,
                'status' => 'aktif',
            ],
            [
                'nama' => 'INSPEKTORAT PATI',
                'kode' => 'INSPEKTORAT-PATI',
                'alamat' => 'Jl. Dr. Setia Budi No.34A, Pati Wetan/Dosoman, Pati Wetan, Kec. Pati, Kabupaten Pati',
                'lat' => -6.7554054,
                'lng' => 111.0398784,
                'status' => 'aktif',
            ],
            [
                'nama' => 'BPKAD PATI',
                'kode' => 'BPKAD-PATI',
                'alamat' => 'Jalan Dokter Setia Budi, Pati Kidul, Pati, Pati Wetan/Dosoman, Pati Wetan, Kec. Pati, Kabupaten Pati',
                'lat' => -6.755376,
                'lng' => 111.039722,
                'status' => 'aktif',
            ],
            [
                'nama' => 'DISTRAN PATI',
                'kode' => 'DISTRAN-PATI',
                'alamat' => 'Jl. Jenderal Sudirman No.70, Pati Kidul, Kec. Pati, Kabupaten Pati',
                'lat' => -6.7516111,
                'lng' => 111.0302626,
                'status' => 'aktif',
            ],
            [
                'nama' => 'DINSOS PATI',
                'kode' => 'DINSOS-PATI',
                'alamat' => 'Jl. Ki Juru Mertani No.59117, RW.01, Cengkok, Sidoharjo, Kec. Pati, Kabupaten Pati',
                'lat' => -6.7536011,
                'lng' => 111.0502085,
                'status' => 'aktif',
            ],
            [
                'nama' => 'RSUD KARTINI JEPARA',
                'kode' => 'RSUD-KARTINI-JEPARA',
                'alamat' => 'Jl. Ki Juru Mertani No.59117, RW.01, Cengkok, Sidoharjo, Kec. Pati, Kabupaten Pati',
                'lat' => -6.60574940,
                'lng' => 110.68186012,
                'status' => 'aktif',
            ],
        ];

        foreach ($instansis as $instansi) {
            Instansi::updateOrCreate(
                ['kode' => $instansi['kode']],
                $instansi
            );
        }

        $this->command->info('Seeded ' . count($instansis) . ' instansi records.');
    }
}