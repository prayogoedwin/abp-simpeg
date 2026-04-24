<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChecklistTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'instansi_id' => 1,
                'name' => 'Template Checklist Cleaning Service Instansi 1',
            ],
            [
                'instansi_id' => 1,
                'name' => 'Template Checklist Satpam Instansi 1',
            ],
            [
                'instansi_id' => 1,
                'name' => 'Template Checklist Office Boy (OB) Instansi 1',
            ],
            [
                'instansi_id' => 2,
                'name' => 'Template Checklist Cleaning Service Instansi 2',
            ],
            [
                'instansi_id' => 2,
                'name' => 'Template Checklist Satpam Instansi 2',
            ],
            [
                'instansi_id' => 2,
                'name' => 'Template Checklist Office Boy (OB) Instansi 2',
            ]


        ];

        foreach ($data as $item) {
            \App\Models\ChecklistTemplate::create($item);
        }
    }
}
