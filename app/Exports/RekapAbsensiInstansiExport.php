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

class RekapAbsensiInstansiExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithEvents
{
    protected $rekapData;
    protected $tanggalList;
    protected $periode;
    protected $statusColors;

    public function __construct($rekapData, $tanggalList, $periode)
    {
        $this->rekapData = $rekapData;
        $this->tanggalList = $tanggalList;
        $this->periode = $periode;
    }

    public function array(): array
    {
        $data = [];
        $this->statusColors = []; // Store status colors for each cell

        foreach ($this->rekapData as $row) {
            $rowData = [$row['member']->name];

            $totalTerlambat = 0;
            $totalLibur = 0;
            $totalIzin = 0;
            $totalAbsen = 0;

            $rowIndex = count($data) + 6; // Row index in Excel (starting from row 6 because of header rows)
            $colIndex = 2; // Start from column B (index 2)

            foreach ($this->tanggalList as $tgl) {
                $absen = $row['absensi'][$tgl['tanggal']] ?? null;

                if ($absen) {
                    $totalAbsen++;

                    if ($absen['status'] == 'terlambat') {
                        $totalTerlambat++;
                        $rowData[] = $absen['jam_masuk'] . ' / ' . $absen['jam_pulang'];
                        // Store color info for this cell
                        $this->statusColors[$rowIndex][$colIndex] = 'terlambat';
                    } elseif ($absen['status'] == 'izin' || $absen['status'] == 'sakit') {
                        $totalIzin++;
                        $rowData[] = $absen['jam_masuk'] . ' / ' . $absen['jam_pulang'];
                        $this->statusColors[$rowIndex][$colIndex] = 'izin';
                    } elseif ($absen['status'] == 'libur' || $absen['status'] == 'cuti') {
                        $totalLibur++;
                        $rowData[] = strtoupper($absen['status']);
                        $this->statusColors[$rowIndex][$colIndex] = 'libur';
                    } elseif ($absen['status'] == 'alpha') {
                        $totalAbsen--;
                        $rowData[] = $absen['jam_masuk'] . ' / ' . $absen['jam_pulang'];
                        $this->statusColors[$rowIndex][$colIndex] = 'alpha';
                    } elseif ($absen['jam_masuk']) {
                        // Normal attendance with jam_masuk but no special status
                        $rowData[] = $absen['jam_masuk'] . ' / ' . $absen['jam_pulang'];
                        $this->statusColors[$rowIndex][$colIndex] = 'normal';
                    } else {
                        $rowData[] = '-';
                        $this->statusColors[$rowIndex][$colIndex] = 'none';
                    }
                } else {
                    $rowData[] = '-';
                    $this->statusColors[$rowIndex][$colIndex] = 'none';
                }

                $colIndex++;
            }

            $rowData[] = $totalTerlambat;
            $this->statusColors[$rowIndex][$colIndex] = 'terlambat';

            $rowData[] = $totalIzin;
            $this->statusColors[$rowIndex][$colIndex + 1] = 'izin';

            $rowData[] = $totalLibur;
            $this->statusColors[$rowIndex][$colIndex + 2] = 'libur';

            $rowData[] = $totalAbsen;
            $this->statusColors[$rowIndex][$colIndex + 3] = 'none';


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

        // Add total column headings
        $headings[] = 'TELAT';
        $headings[] = 'IZIN';
        $headings[] = 'LIBUR';
        $headings[] = 'TOTAL';

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
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Header info
                $sheet->insertNewRowBefore(1, 4);
                $sheet->setCellValue('A1', 'REKAP ABSENSI');
                $sheet->setCellValue('A2', 'Instansi: ' . ($this->periode['instansi'] ?? '-'));
                $sheet->setCellValue('A3', 'Periode: ' . $this->periode['bulan_nama'] . ' ' . $this->periode['tahun']);

                // Style header
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                // Get last column index (numeric)
                $lastColumnIndex = count($this->tanggalList) + 1 + 4; // +1 for Nama column + 4 for totals
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


                // Apply status-based colors
                if (isset($this->statusColors)) {
                    foreach ($this->statusColors as $rowIndex => $columns) {
                        foreach ($columns as $colIndex => $status) {
                            $cellCoordinate = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;

                            switch ($status) {
                                case 'terlambat':
                                    $sheet->getStyle($cellCoordinate)->applyFromArray([
                                        'fill' => [
                                            'fillType' => Fill::FILL_SOLID,
                                            'startColor' => ['rgb' => 'FFCCCC'], // Light red
                                        ],

                                    ]);
                                    break;
                                case 'libur':
                                    $sheet->getStyle($cellCoordinate)->applyFromArray([
                                        'fill' => [
                                            'fillType' => Fill::FILL_SOLID,
                                            'startColor' => ['rgb' => 'FFEEAA'], // Light yellow
                                        ],

                                    ]);
                                    break;
                                case 'izin':
                                    $sheet->getStyle($cellCoordinate)->applyFromArray([
                                        'fill' => [
                                            'fillType' => Fill::FILL_SOLID,
                                            'startColor' => ['rgb' => 'CCFFCC'], // Light green
                                        ],

                                    ]);
                                    break;
                                case 'alpha':
                                    $sheet->getStyle($cellCoordinate)->applyFromArray([
                                        'fill' => [
                                            'fillType' => Fill::FILL_SOLID,
                                            'startColor' => ['rgb' => 'E0E0E0'], // Light gray
                                        ],

                                    ]);
                                    break;
                            }
                        }
                    }
                }
            },
        ];
    }
}
