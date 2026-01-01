<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\Member;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapAbsensiExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected int $memberId;
    protected int $bulan;
    protected int $tahun;

    public function __construct(int $memberId, int $bulan, int $tahun)
    {
        $this->memberId = $memberId;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function array(): array
    {
        $rekap = Absensi::getRekapBulanan($this->memberId, $this->bulan, $this->tahun);
        
        return collect($rekap)->map(fn ($row) => [
            $row['tanggal']->format('d/m/Y'),
            $row['hari'],
            $row['jam_masuk'],
            $row['jam_pulang'],
            $row['status'],
            $row['keterangan'],
        ])->toArray();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Hari',
            'Jam Masuk',
            'Jam Pulang',
            'Status',
            'Keterangan',
        ];
    }

    public function title(): string
    {
        $member = Member::find($this->memberId);
        return substr($member?->name ?? 'Rekap', 0, 31);
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