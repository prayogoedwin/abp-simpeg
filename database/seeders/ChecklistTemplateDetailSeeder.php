<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChecklistTemplateDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // Template Checklist Cleaning Service Instansi 1
            [
                'checklist_template_id' => 1,
                'label' => 'Nama Pegawai',
                'type' => 'text',
            ],
            [
                'checklist_template_id' => 1,
                'label' => 'Area',
                'type' => 'checkbox',
                'options' => 'Belakang Wijaya Kusuma, Depan Lift, Depan Wijaya, Depan Sakura, Samping Edelweis, Depan Lavender, Depan Seruni, Belakang Tulip' // comma separated options for checkbox
            ],
            [
                'checklist_template_id' => 1,
                'label' => 'Pekerjaan',
                'type' => 'select',
                'options' => 'Pembersihan, Penataan dan Perawatan Taman, Pembersihan Got serta Pengumpulan sampah, Pruning tanaman yang diperlukan, Membersihkan rumput dan gulma, Penyiraman kepada tanaman'
            ],
            [
                'checklist_template_id' => 1,
                'label' => 'Nama Pengawas',
                'type' => 'radio',
                'options' => 'Ahmad Ferry, Budi Santoso, Citra Dewi'
            ],
            // Template Checklist Satpam Instansi 1
            [
                'checklist_template_id' => 2,
                'label' => 'Nama',
                'type' => 'text',
            ],
            [
                'checklist_template_id' => 2,
                'label' => 'Apakah petugas satpam mengenakan seragam dengan rapi dan lengkap?',
                'type' => 'checkbox',
            ],
            [
                'checklist_template_id' => 2,
                'label' => 'Apakah petugas satpam memeriksa identitas pengunjung dengan benar?',
                'type' => 'checkbox',
            ],
            [
                'checklist_template_id' => 2,
                'label' => 'Nama Pengawas',
                'type' => 'radio',
                'options' => 'Ahmad Ferry, Budi Santoso, Citra Dewi'
            ],
            // Template Checklist Office Boy (OB) Instansi 1
            [
                'checklist_template_id' => 3,
                'label' => 'Nama',
                'type' => 'text',
            ],
            [
                'checklist_template_id' => 3,
                'label' => 'Pekerjaan',
                'type' => 'select',
                'options' => 'Kebersihan dan Kerapihan, Pelayanan Konsumsi, Tugas Pendukung Administrasi, Tugas Pendukung Operasional, Tugas Pendukung Lainnya'
            ],
            [
                'checklist_template_id' => 3,
                'label' => 'Nama Pengawas',
                'type' => 'radio',
                'options' => 'Ahmad Ferry, Budi Santoso, Citra Dewi'
            ],


            [
                'checklist_template_id' => 4,
                'label' => 'Nama Pegawai',
                'type' => 'text',
            ],
            [
                'checklist_template_id' => 4,
                'label' => 'Area',
                'type' => 'checkbox',
                'options' => 'Belakang Wijaya Kusuma, Depan Lift, Depan Wijaya, Depan Sakura, Samping Edelweis, Depan Lavender, Depan Seruni, Belakang Tulip' // comma separated options for checkbox
            ],
            [
                'checklist_template_id' => 4,
                'label' => 'Pekerjaan',
                'type' => 'select',
                'options' => 'Pembersihan, Penataan dan Perawatan Taman, Pembersihan Got serta Pengumpulan sampah, Pruning tanaman yang diperlukan, Membersihkan rumput dan gulma, Penyiraman kepada tanaman'
            ],
            [
                'checklist_template_id' => 4,
                'label' => 'Nama Pengawas',
                'type' => 'radio',
                'options' => 'Ahmad Ferry, Budi Santoso, Citra Dewi'
            ],
            // Template Checklist Satpam Instansi 1
            [
                'checklist_template_id' => 5,
                'label' => 'Nama',
                'type' => 'text',
            ],
            [
                'checklist_template_id' => 5,
                'label' => 'Apakah petugas satpam mengenakan seragam dengan rapi dan lengkap?',
                'type' => 'checkbox',
            ],
            [
                'checklist_template_id' => 5,
                'label' => 'Apakah petugas satpam memeriksa identitas pengunjung dengan benar?',
                'type' => 'checkbox',
            ],
            [
                'checklist_template_id' => 5,
                'label' => 'Nama Pengawas',
                'type' => 'radio',
                'options' => 'Ahmad Ferry, Budi Santoso, Citra Dewi'
            ],
            // Template Checklist Office Boy (OB) Instansi 1
            [
                'checklist_template_id' => 6,
                'label' => 'Nama',
                'type' => 'text',
            ],
            [
                'checklist_template_id' => 6,
                'label' => 'Pekerjaan',
                'type' => 'select',
                'options' => 'Kebersihan dan Kerapihan, Pelayanan Konsumsi, Tugas Pendukung Administrasi, Tugas Pendukung Operasional, Tugas Pendukung Lainnya'
            ],
            [
                'checklist_template_id' => 6,
                'label' => 'Nama Pengawas',
                'type' => 'radio',
                'options' => 'Ahmad Ferry, Budi Santoso, Citra Dewi'
            ],
        ];

        foreach ($data as $item) {
            \App\Models\ChecklistTemplateDetail::create($item);
        }
    }
}
