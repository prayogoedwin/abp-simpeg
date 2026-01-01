<?php

namespace App\Filament\Resources\Absensis\Pages;

use App\Filament\Resources\Absensis\AbsensiResource;
use App\Models\Absensi;
use App\Models\Member;
use App\Models\Instansi;
use App\Exports\RekapAbsensiExport;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Computed;
use Carbon\Carbon;

class RekapAbsensi extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = AbsensiResource::class;

    protected static ?string $title = 'Rekap Absensi';

    public ?int $instansi_id = null;
    public ?int $member_id = null;
    public ?int $bulan = null;
    public ?int $tahun = null;

    public function getView(): string
    {
        return 'filament.resources.absensis.pages.rekap-absensi';
    }

    public function mount(): void
    {
        $this->bulan = now()->month;
        $this->tahun = now()->year;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(4)
                    ->schema([
                        Select::make('instansi_id')
                            ->label('Instansi')
                            ->options(Instansi::query()->pluck('nama', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn () => $this->member_id = null),

                        Select::make('member_id')
                            ->label('Pegawai')
                            ->options(function () {
                                $query = Member::query();
                                if ($this->instansi_id) {
                                    $query->where('instansi_id', $this->instansi_id);
                                }
                                return $query->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->live(),

                        Select::make('bulan')
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                            ])
                            ->default(now()->month)
                            ->live(),

                        Select::make('tahun')
                            ->label('Tahun')
                            ->options(function () {
                                $years = [];
                                for ($i = now()->year - 2; $i <= now()->year + 1; $i++) {
                                    $years[$i] = $i;
                                }
                                return $years;
                            })
                            ->default(now()->year)
                            ->live(),
                    ]),
            ]);
    }

    #[Computed]
    public function members(): array
    {
        if (!$this->instansi_id) {
            return [];
        }

        return Member::where('instansi_id', $this->instansi_id)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    #[Computed]
    public function selectedMember(): ?Member
    {
        if (!$this->member_id) {
            return null;
        }

        return Member::with('instansi')->find($this->member_id);
    }

    #[Computed]
    public function rekapData(): array
    {
        if (!$this->member_id || !$this->bulan || !$this->tahun) {
            return [];
        }

        return Absensi::getRekapBulanan($this->member_id, $this->bulan, $this->tahun);
    }

    #[Computed]
    public function summaryData(): array
    {
        if (!$this->member_id || !$this->bulan || !$this->tahun) {
            return [];
        }

        return Absensi::getSummaryBulanan($this->member_id, $this->bulan, $this->tahun);
    }

    public function exportExcel()
    {
        if (!$this->member_id) {
            return;
        }

        $member = Member::find($this->member_id);
        $filename = "rekap_absensi_{$member->name}_{$this->bulan}_{$this->tahun}.xlsx";

        return Excel::download(
            new RekapAbsensiExport($this->member_id, $this->bulan, $this->tahun),
            $filename
        );
    }

    public function exportPdf()
    {
        if (!$this->member_id) {
            return;
        }

        $member = Member::with('instansi')->find($this->member_id);
        $rekap = Absensi::getRekapBulanan($this->member_id, $this->bulan, $this->tahun);
        $summary = Absensi::getSummaryBulanan($this->member_id, $this->bulan, $this->tahun);

        $pdf = Pdf::loadView('exports.rekap-absensi-pdf', [
            'member' => $member,
            'rekap' => $rekap,
            'summary' => $summary,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
        ]);

        $filename = "rekap_absensi_{$member->name}_{$this->bulan}_{$this->tahun}.pdf";

        return response()->streamDownload(fn () => print($pdf->output()), $filename);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action('exportExcel')
                ->visible(fn () => $this->member_id !== null),

            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document')
                ->color('danger')
                ->action('exportPdf')
                ->visible(fn () => $this->member_id !== null),
        ];
    }
}