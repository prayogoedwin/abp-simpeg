<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@gmail.com',
                'password' => '12345678',
                'whatsapp' => '081234567890',
                'alamat' => 'Jl. Merdeka No. 10, Jakarta',
                'nik' => '1234567890123456',
                'instansi_id' => 1,
                'posisi_id' => 2,
                'status' => 1,
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti.aminah@gmail.com',
                'password' => '12345678',
                'whatsapp' => '081234567891',
                'alamat' => 'Jl. Mawar No. 5, Bandung',
                'nik' => '1234567890123457',
                'instansi_id' => rand(1, 9),
                'posisi_id' => rand(1, 6),
                'status' => 1,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@gmail.com',
                'password' => '12345678',
                'whatsapp' => '081234567892',
                'alamat' => 'Jl. Melati No. 12, Surabaya',
                'nik' => '1234567890123458',
                'instansi_id' => rand(1, 9),
                'posisi_id' => rand(1, 6),
                'status' => 1,
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@gmail.com',
                'password' => '12345678',
                'whatsapp' => '081234567893',
                'alamat' => 'Jl. Kenanga No. 8, Yogyakarta',
                'nik' => '1234567890123459',
                'instansi_id' => rand(1, 9),
                'posisi_id' => rand(1, 6),
                'status' => 1,
            ],
            [
                'name' => 'Rian Hidayat',
                'email' => 'rian.hidayat@gmail.com',
                'password' => '12345678',
                'whatsapp' => '081234567894',
                'alamat' => 'Jl. Anggrek No. 15, Semarang',
                'nik' => '1234567890123460',
                'instansi_id' => rand(1, 9),
                'posisi_id' => rand(1, 6),
                'status' => 1,
            ],
            [
                'name' => 'Lani Wijaya',
                'email' => 'lani.wijaya@gmail.com',
                'password' => '12345678',
                'whatsapp' => '081234567895',
                'alamat' => 'Jl. Dahlia No. 3, Malang',
                'nik' => '1234567890123461',
                'instansi_id' => rand(1, 9),
                'posisi_id' => rand(1, 6),
                'status' => 1,
            ],
            [
                'name' => 'Andi Wijaya',
                'email' => 'andi.wijaya@gmail.com',
                'password' => '12345678',
                'whatsapp' => '081234567896',
                'alamat' => 'Jl. Kamboja No. 20, Bali',
                'nik' => '1234567890123462',
                'instansi_id' => rand(1, 9),
                'posisi_id' => rand(1, 6),
                'status' => 1,
            ],
            
        ];

        foreach ($data as $member) {
            \App\Models\Member::create($member);
        }
    }
}
