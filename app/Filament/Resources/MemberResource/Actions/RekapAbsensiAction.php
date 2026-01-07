<?php

namespace App\Filament\Resources\MemberResource\Actions;

use App\Models\Member;
use App\Models\Absensi;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Illuminate\Contracts\View\View;

class RekapAbsensiAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('rekapAbsensi');
        $this->label('Rekap Absensi');
        $this->icon('heroicon-o-calendar-days');
        $this->color('info');
        $this->modalHeading(fn (Member $record) => "Rekap Absensi - {$record->name}");
        $this->modalWidth('7xl');
        $this->modalSubmitAction(false);
        $this->modalCancelActionLabel('Tutup');

        $this->form([
            Select::make('bulan')
                ->label('Bulan')
                ->options([
                    1 => 'Januari',
                    2 => 'Februari',
                    3 => 'Maret',
                    4 => 'April',
                    5 => 'Mei',
                    6 => 'Juni',
                    7 => 'Juli',
                    8 => 'Agustus',
                    9 => 'September',
                    10 => 'Oktober',
                    11 => 'November',
                    12 => 'Desember',
                ])
                ->default(now()->month)
                ->required()
                ->live(),

            Select::make('tahun')
                ->label('Tahun')
                ->options(function () {
                    $years = [];
                    $currentYear = now()->year;
                    for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++) {
                        $years[$i] = $i;
                    }
                    return $years;
                })
                ->default(now()->year)
                ->required()
                ->live(),
        ]);

        $this->modalContent(function (Member $record, Get $get): View {
            $bulan = $get('bulan') ?? now()->month;
            $tahun = $get('tahun') ?? now()->year;

            $rekap = Absensi::getRekapBulanan($record->id, $bulan, $tahun);
            $summary = Absensi::getSummaryBulanan($record->id, $bulan, $tahun);

            $periode = [
                'bulan' => (int) $bulan,
                'bulan_nama' => Carbon::create($tahun, $bulan, 1)->translatedFormat('F'),
                'tahun' => (int) $tahun,
            ];

            return view('filament.resources.member.rekap-absensi', [
                'member' => $record,
                'rekap' => $rekap,
                'summary' => $summary,
                'periode' => $periode,
            ]);
        });
    }

    public static function getDefaultName(): ?string
    {
        return 'rekapAbsensi';
    }
}