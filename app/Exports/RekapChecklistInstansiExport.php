<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class RekapChecklistInstansiExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithEvents
{
    protected $rekapData;
    protected $tanggalList;
    protected $periode;

    public function __construct($rekapData, $tanggalList, $periode)
    {
        $this->rekapData = $rekapData;
        $this->tanggalList = $tanggalList;
        $this->periode = $periode;

        // dd($this->rekapData, $this->tanggalList, $this->periode);
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->rekapData as $row) {
            $rowData = [$row['member']->name];
            
            foreach ($this->tanggalList as $tgl) {
                $checklist = $row['checklist'][$tgl['tanggal']] ?? null;
                
                if ($checklist && $checklist['isAny']) {
                    $rowData[] = 'Ada Checklist';
                } else {
                    $rowData[] = '-';
                }
            }
            
            $data[] = $rowData;
        }

        return $data;
    }

    public function headings(): array
    {
        $headings = ['Nama'];
        
        foreach ($this->tanggalList as $tgl) {
            $headings[] = $tgl['tanggal'] . ' (' . $tgl['hari'] . ')';
        }
        
        return $headings;
    }

    public function title(): string
    {
        return 'Rekap ' . $this->periode['bulan_nama'] . ' ' . $this->periode['tahun'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Header info
                $sheet->insertNewRowBefore(1, 4);
                $sheet->setCellValue('A1', 'REKAP ABSENSI');
                $sheet->setCellValue('A2', 'Instansi: ' . ($this->periode['instansi'] ?? '-'));
                $sheet->setCellValue('A3', 'Periode: ' . $this->periode['bulan_nama'] . ' ' . $this->periode['tahun']);
                
                // Style header
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                
                // Get last column index (numeric)
                $lastColumnIndex = count($this->tanggalList) + 1; // +1 for Nama column
                $lastColumn = Coordinate::stringFromColumnIndex($lastColumnIndex);
                
                // Auto size columns using column index
                for ($col = 1; $col <= $lastColumnIndex; $col++) {
                    $colLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
                }
                
                // Border
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A5:' . $lastColumn . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
                
                // Center alignment for data (skip first column)
                $sheet->getStyle('B6:' . $lastColumn . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}