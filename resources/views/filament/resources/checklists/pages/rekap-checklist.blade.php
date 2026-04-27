<x-filament-panels::page>
    {{-- Filter Form --}}
    <form wire:submit="submit">
        {{ $this->form }}
        
        <div style="margin-top: 1rem;">
            <x-filament::button type="submit" icon="heroicon-o-magnifying-glass">
                Tampilkan Rekap
            </x-filament::button>
        </div>
    </form>

    @if($showData)
        {{-- Info Periode --}}
        <div style="margin-top: 1.5rem; margin-bottom: 1rem; padding: 1rem; background: rgba(79, 70, 229, 0.1); border-radius: 0.5rem;">
            <div style="font-size: 1.125rem; font-weight: 600; color: #4F46E5;">
                {{ $periode['instansi'] ?? '-' }}
            </div>
            <div style="font-size: 0.875rem; color: #6b7280;">
                Periode: {{ $periode['bulan_nama'] }} {{ $periode['tahun'] }}
            </div>
        </div>

        {{-- Rekap Table --}}
        <x-filament::section>
            <x-slot name="heading">
                Rekap Kehadiran
            </x-slot>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem; min-width: 1500px;">
                    <thead>
                        <tr style="background: #4F46E5;">
                            <th style="padding: 0.5rem; text-align: left; color: white; font-weight: 600; position: sticky; left: 0; background: #4F46E5; z-index: 10; min-width: 150px;">
                                Nama
                            </th>
                            @foreach($tanggalList as $tgl)
                                <th style="padding: 0.5rem; text-align: center; color: white; font-weight: 600; min-width: 60px; {{ $tgl['is_weekend'] ? 'background: #6366f1;' : '' }}">
                                    <div>{{ $tgl['tanggal'] }}</div>
                                    <div style="font-size: 0.625rem; font-weight: 400;">{{ $tgl['hari'] }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapData as $row)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                                <td style="padding: 0.5rem; white-space: nowrap; font-weight: 500; position: sticky; left: 0; background: #1f2937; z-index: 5;">
                                    {{ $row['member']->name }}
                                </td>
                                @foreach($tanggalList as $tgl)
                                    @php
                                        $absen = $row['absensi'][$tgl['tanggal']] ?? null;
                                        $bgColor = 'transparent';
                                        
                                        if ($tgl['is_weekend']) {
                                            $bgColor = 'rgba(107, 114, 128, 0.2)';
                                        } elseif ($absen && $absen['status']) {
                                            $bgColor = match($absen['status']) {
                                                'hadir' => 'rgba(34, 197, 94)',
                                                'telat' => 'rgba(234, 179, 8)',
                                                'izin' => 'rgba(59, 130, 246,)',
                                                'sakit' => 'rgba(168, 85, 247)',
                                                'alpha' => 'rgba(239, 68, 68)',
                                                default => 'transparent',
                                            };
                                        }
                                    @endphp
                                    <td style="padding: 0.25rem; text-align: center; background: {{ $bgColor }}; vertical-align: top;">
                                        
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($tanggalList) + 1 }}" style="padding: 2rem; text-align: center; color: #6b7280;">
                                    Tidak ada data pegawai di instansi ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Legend --}}
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1); display: flex; gap: 1.5rem; flex-wrap: wrap; font-size: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 1rem; height: 1rem; background: rgba(34, 197, 94, 0.3); border-radius: 0.25rem;"></div>
                    <span style="color: #9ca3af;">Hadir</span>
                </div>
                
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 1rem; height: 1rem; background: rgba(239, 68, 68, 0.3); border-radius: 0.25rem;"></div>
                    <span style="color: #9ca3af;">Alpha</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 1rem; height: 1rem; background: rgba(107, 114, 128, 0.3); border-radius: 0.25rem;"></div>
                    <span style="color: #9ca3af;">Weekend/Libur</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-left: auto;">
                    <span style="color: #22c55e;">Hijau = Masuk</span>
                    <span style="color: #9ca3af;">|</span>
                    <span style="color: #ef4444;">Merah = Pulang</span>
                </div>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>