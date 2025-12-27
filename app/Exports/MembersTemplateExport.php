<?php

namespace App\Exports;

use App\Models\Instansi;
use App\Models\Posisi;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class MembersTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Data Pegawai' => new MembersTemplateSheet(),
            'Referensi' => new MembersReferenceSheet(),
        ];
    }
}

class MembersTemplateSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    public function array(): array
    {
        // Return empty karena akan diisi manual via event
        return [];
    }

    public function headings(): array
    {
        return [
            'no_karyawan',
            'nik',
            'nama_lengkap',
            'jenis_kelamin',
            'tanggal_lahir',
            'alamat',
            'email',
            'whatsapp',
            'instansi',
            'posisi',
            'tanggal_masuk',
            'tanggal_kontrak_berakhir',
            'status_kepegawaian',
            'status_akun',
            'password',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Data contoh
                $data = [
                    [
                        'EMP001',
                        '3201234567890001',
                        'John Doe',
                        'L',
                        '15/05/1990',
                        'Jl. Contoh No. 123',
                        'john@example.com',
                        '081234567890',
                        'Dinas Pendidikan',
                        'Satpam',
                        '01/01/2024',
                        '31/12/2024',
                        'aktif',
                        'aktif',
                        '12345678',
                    ],
                    [
                        'EMP002',
                        '3201234567890002',
                        'Jane Smith',
                        'P',
                        '20/08/1995',
                        'Jl. Sample No. 456',
                        'jane@example.com',
                        '089876543210',
                        'Dinas Kesehatan',
                        'Cleaning Service',
                        '01/02/2024',
                        '31/01/2025',
                        'aktif',
                        'aktif',
                        '12345678',
                    ],
                ];

                $row = 2; // Mulai dari row 2 (setelah header)
                foreach ($data as $rowData) {
                    $col = 1;
                    foreach ($rowData as $index => $value) {
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        
                        // Kolom yang perlu diformat sebagai text: B (nik), H (whatsapp)
                        if ($col == 2 || $col == 8) {
                            $cell->setValueExplicit($value, DataType::TYPE_STRING);
                        } else {
                            $cell->setValue($value);
                        }
                        $col++;
                    }
                    $row++;
                }
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header style
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
        ]);

        // Tambah komentar/catatan di header
        $sheet->getComment('B1')->getText()->createTextRun('16 digit, format text');
        $sheet->getComment('C1')->getText()->createTextRun('Wajib diisi');
        $sheet->getComment('D1')->getText()->createTextRun('L = Laki-laki, P = Perempuan');
        $sheet->getComment('E1')->getText()->createTextRun('Format: dd/mm/yyyy');
        $sheet->getComment('H1')->getText()->createTextRun('Format text, contoh: 081234567890');
        $sheet->getComment('I1')->getText()->createTextRun('Nama instansi harus sesuai dengan data master');
        $sheet->getComment('J1')->getText()->createTextRun('Nama posisi harus sesuai dengan data master');
        $sheet->getComment('K1')->getText()->createTextRun('Format: dd/mm/yyyy');
        $sheet->getComment('L1')->getText()->createTextRun('Format: dd/mm/yyyy');
        $sheet->getComment('M1')->getText()->createTextRun('Pilihan: aktif, nonaktif, cuti, resign');
        $sheet->getComment('N1')->getText()->createTextRun('Pilihan: aktif, nonaktif');
        $sheet->getComment('O1')->getText()->createTextRun('Default: 12345678 jika kosong');

        return [];
    }
}

class MembersReferenceSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        $instansis = Instansi::where('status', 'aktif')->pluck('nama')->toArray();
        $posisis = Posisi::where('status', 'aktif')->pluck('nama')->toArray();

        $maxRows = max(count($instansis), count($posisis), 4);
        $data = [];

        $jenisKelamin = ['L - Laki-laki', 'P - Perempuan'];
        $statusKepegawaian = ['aktif', 'nonaktif', 'cuti', 'resign'];
        $statusAkun = ['aktif', 'nonaktif'];

        for ($i = 0; $i < $maxRows; $i++) {
            $data[] = [
                $instansis[$i] ?? '',
                $posisis[$i] ?? '',
                $jenisKelamin[$i] ?? '',
                $statusKepegawaian[$i] ?? '',
                $statusAkun[$i] ?? '',
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Daftar Instansi',
            'Daftar Posisi',
            'Jenis Kelamin',
            'Status Kepegawaian',
            'Status Akun',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '10B981'],
            ],
        ]);

        return [];
    }
}