<?php

namespace App\Exports;

use App\Models\Member;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class MembersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $members;

    public function __construct()
    {
        $this->members = Member::with(['instansi', 'posisi'])->get();
    }

    public function collection()
    {
        // Return empty collection karena data akan diisi via event
        return collect([]);
    }

    public function headings(): array
    {
        return [
            'No',
            'No Karyawan',
            'NIK',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Tanggal Lahir',
            'Alamat',
            'Email',
            'WhatsApp',
            'Instansi',
            'Posisi',
            'Tanggal Masuk',
            'Tanggal Kontrak Berakhir',
            'Status Kepegawaian',
            'Status Akun',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $row = 2;
                $no = 1;
                
                foreach ($this->members as $member) {
                    $data = [
                        $no,
                        $member->no_karyawan,
                        $member->nik,
                        $member->name,
                        $member->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan',
                        $member->tanggal_lahir?->format('d/m/Y'),
                        $member->alamat,
                        $member->email,
                        $member->whatsapp,
                        $member->instansi?->nama,
                        $member->posisi?->nama,
                        $member->tanggal_masuk?->format('d/m/Y'),
                        $member->tanggal_kontrak_berakhir?->format('d/m/Y'),
                        $member->status_kepegawaian,
                        $member->status ? 'Aktif' : 'Nonaktif',
                    ];

                    $col = 1;
                    foreach ($data as $value) {
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        
                        // Kolom B (No Karyawan), C (NIK), I (WhatsApp) sebagai text
                        if (in_array($col, [2, 3, 9])) {
                            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
                        } else {
                            $cell->setValue($value);
                        }
                        $col++;
                    }
                    
                    $row++;
                    $no++;
                }
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ],
        ];
    }
}