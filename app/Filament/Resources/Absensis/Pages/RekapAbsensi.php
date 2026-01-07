<?php

namespace App\Filament\Resources\Absensis\Pages;

use App\Filament\Resources\Absensis\AbsensiResource;
use App\Models\Absensi;
use App\Models\Member;
use App\Models\Instansi;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Livewire\Attributes\Url;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class RekapAbsensi extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = AbsensiResource::class;

    protected static ?string $title = 'Rekap Absensi';

    #[Url]
    public ?int $instansi_id = null;

    #[Url]
    public ?int $bulan = null;

    #[Url]
    public ?int $tahun = null;

    public ?array $filterData = [];

    public array $rekapData = [];
    public array $tanggalList = [];
    public array $periode = [];
    public bool $showData = false;

    public function getView(): string
    {
        return 'filament.resources.absensis.pages.rekap-absensi';
    }

    public function mount(): void
    {
        $this->bulan = $this->bulan ?? now()->month;
        $this->tahun = $this->tahun ?? now()->year;

        $this->filterData = [
            'instansi_id' => $this->instansi_id,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
        ];

        if ($this->instansi_id) {
            $this->loadRekap();
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter')
                    ->schema([
                        Select::make('instansi_id')
                            ->label('Instansi')
                            ->options(Instansi::query()->pluck('nama', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Pilih Instansi'),

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
                            ->native(false),

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
                            ->native(false),
                    ])
                    ->columns(3)
                    ->compact(),
            ])
            ->statePath('filterData');
    }

    public function submit(): void
    {
        $this->instansi_id = $this->filterData['instansi_id'];
        $this->bulan = $this->filterData['bulan'];
        $this->tahun = $this->filterData['tahun'];
        
        $this->loadRekap();
    }

    protected function loadRekap(): void
    {
        if (!$this->instansi_id) {
            $this->showData = false;
            return;
        }

        // Generate list tanggal dalam bulan
        $startDate = Carbon::create($this->tahun, $this->bulan, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        $this->tanggalList = [];
        $period = CarbonPeriod::create($startDate, $endDate);
        
        foreach ($period as $date) {
            $this->tanggalList[] = [
                'tanggal' => $date->day,
                'hari' => $date->translatedFormat('D'),
                'full_date' => $date->format('Y-m-d'),
                'is_weekend' => $date->isWeekend(),
            ];
        }

        // Ambil semua member di instansi
        $members = Member::where('instansi_id', $this->instansi_id)
            ->where('status_kepegawaian', 'aktif')
            ->orderBy('name')
            ->get();

        // Ambil semua absensi dalam periode
        $absensis = Absensi::where('instansi_id', $this->instansi_id)
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->groupBy('member_id');

        $this->rekapData = [];

        foreach ($members as $member) {
            $memberAbsensi = $absensis->get($member->id, collect());
            $absensiByDate = $memberAbsensi->keyBy(fn ($a) => Carbon::parse($a->tanggal)->format('Y-m-d'));

            $row = [
                'member' => $member,
                'absensi' => [],
            ];

            foreach ($this->tanggalList as $tgl) {
                $absen = $absensiByDate->get($tgl['full_date']);
                
                $row['absensi'][$tgl['tanggal']] = [
                    'jam_masuk' => $absen ? ($absen->jam_masuk ? Carbon::parse($absen->jam_masuk)->format('H:i') : '-') : null,
                    'jam_pulang' => $absen ? ($absen->jam_pulang ? Carbon::parse($absen->jam_pulang)->format('H:i') : '-') : null,
                    'status' => $absen?->status,
                    'is_weekend' => $tgl['is_weekend'],
                ];
            }

            $this->rekapData[] = $row;
        }

        $this->periode = [
            'bulan' => $this->bulan,
            'bulan_nama' => Carbon::create($this->tahun, $this->bulan, 1)->translatedFormat('F'),
            'tahun' => $this->tahun,
            'instansi' => Instansi::find($this->instansi_id)?->nama,
        ];

        $this->showData = true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->visible(fn () => $this->showData)
                ->action(fn () => $this->exportExcel()),

            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->visible(fn () => $this->showData)
                ->action(fn () => $this->exportPdf()),
        ];
    }

    public function exportExcel()
    {
        $filename = 'rekap-absensi-' . str()->slug($this->periode['instansi'] ?? 'all') . '-' . $this->bulan . '-' . $this->tahun . '.xlsx';
        
        return Excel::download(
            new \App\Exports\RekapAbsensiInstansiExport(
                $this->rekapData,
                $this->tanggalList,
                $this->periode
            ),
            $filename
        );
    }

    public function exportPdf()
    {
        // Siapkan data ringan untuk PDF (tanpa object Eloquent)
        $pdfData = [];
        foreach ($this->rekapData as $row) {
            $pdfData[] = [
                'nama' => $row['member']->name,
                'absensi' => $row['absensi'],
            ];
        }

        $filename = 'rekap-absensi-' . str()->slug($this->periode['instansi'] ?? 'all') . '-' . $this->bulan . '-' . $this->tahun . '.pdf';

        // Increase memory limit temporarily
        ini_set('memory_limit', '256M');

        $pdf = Pdf::loadView('exports.rekap-absensi-instansi-pdf', [
            'rekapData' => $pdfData,
            'tanggalList' => $this->tanggalList,
            'periode' => $this->periode,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename
        );
    }

    public function getTitle(): string
    {
        return "Rekap Absensi";
    }
}