<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\Member;
use App\Models\Absensi;
use App\Exports\RekapAbsensiExport;
use App\Exports\RekapChecklistExport;
use App\Models\Checklist;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Livewire\Attributes\Url;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class RekapChecklist extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithRecord;

    protected static string $resource = MemberResource::class;

    protected static ?string $title = 'Rekap Checklist';

    #[Url]
    public ?int $bulan = null;

    #[Url]
    public ?int $tahun = null;

    public ?array $filterData = [];

    public array $rekap = [];
    public array $summary = [];
    public array $periode = [];

    public function getView(): string
    {
        return 'filament.resources.member.pages.rekap-checklist';
    }

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->bulan = $this->bulan ?? now()->month;
        $this->tahun = $this->tahun ?? now()->year;

        $this->filterData = [
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
        ];

        $this->loadRekapChecklist();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Periode')
                    ->schema([
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
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn () => $this->applyFilter()),

                        Select::make('tahun')
                            ->label('Tahun')
                            ->options(function () {
                                $years = [];
                                $currentYear = now()->year;
                                for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++) {
                                    $years[$i] = (string) $i;
                                }
                                return $years;
                            })
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn () => $this->applyFilter()),
                    ])
                    ->columns(2)
                    ->compact(),
            ])
            ->statePath('filterData');
    }

    public function applyFilter(): void
    {
        $this->bulan = $this->filterData['bulan'];
        $this->tahun = $this->filterData['tahun'];
        $this->loadRekapChecklist();
    }

    protected function loadRekapChecklist(): void
    {
        $this->rekap = Checklist::getRekapBulanan($this->record->id, $this->bulan, $this->tahun);
        // $this->summary = Checklist::getSummaryBulanan($this->record->id, $this->bulan, $this->tahun);
        
        $this->periode = [
            'bulan' => $this->bulan,
            'bulan_nama' => Carbon::create($this->tahun, $this->bulan, 1)->translatedFormat('F'),
            'tahun' => $this->tahun,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(fn () => $this->exportExcel()),

            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->action(fn () => $this->exportPdf()),

            Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->url(MemberResource::getUrl('index'))
                ->color('gray'),
        ];
    }

    public function exportExcel()
    {
        $filename = 'rekap-checklist-' . str()->slug($this->record->name) . '-' . $this->bulan . '-' . $this->tahun . '.xlsx';
        
        return Excel::download(
            new RekapChecklistExport($this->record->id, $this->bulan, $this->tahun),
            $filename
        );
    }

    public function exportPdf()
    {
        $filename = 'rekap-checklist-' . str()->slug($this->record->name) . '-' . $this->bulan . '-' . $this->tahun . '.pdf';

        $pdf = Pdf::loadView('exports.rekap-checklist-pdf', [
            'member' => $this->record,
            'rekap' => $this->rekap,
            'summary' => $this->summary,
            'periode' => $this->periode,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename
        );
    }

    public function getTitle(): string
    {
        return "Rekap Checklist - {$this->record->name}";
    }

    public function getBreadcrumbs(): array
    {
        return [
            MemberResource::getUrl() => 'Pegawai',
            '#' => $this->record->name,
            '' => 'Rekap Checklist',
        ];
    }
}