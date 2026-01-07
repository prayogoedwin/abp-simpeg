<x-filament-panels::page>
    {{-- Filter Form --}}
    {{ $this->form }}

    {{-- Summary Cards --}}
    <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 1rem; margin-top: 1.5rem; margin-bottom: 1.5rem;">
        <div style="background: rgba(34, 197, 94, 0.1); border-radius: 0.75rem; padding: 1rem; text-align: center;">
            <div style="font-size: 1.875rem; font-weight: 700; color: #22c55e;">{{ $summary['hadir'] ?? 0 }}</div>
            <div style="font-size: 0.875rem; color: #9ca3af; margin-top: 0.25rem;">Hadir</div>
        </div>

        <div style="background: rgba(234, 179, 8, 0.1); border-radius: 0.75rem; padding: 1rem; text-align: center;">
            <div style="font-size: 1.875rem; font-weight: 700; color: #eab308;">{{ $summary['telat'] ?? 0 }}</div>
            <div style="font-size: 0.875rem; color: #9ca3af; margin-top: 0.25rem;">Telat</div>
        </div>

        <div style="background: rgba(59, 130, 246, 0.1); border-radius: 0.75rem; padding: 1rem; text-align: center;">
            <div style="font-size: 1.875rem; font-weight: 700; color: #3b82f6;">{{ $summary['izin'] ?? 0 }}</div>
            <div style="font-size: 0.875rem; color: #9ca3af; margin-top: 0.25rem;">Izin</div>
        </div>

        <div style="background: rgba(168, 85, 247, 0.1); border-radius: 0.75rem; padding: 1rem; text-align: center;">
            <div style="font-size: 1.875rem; font-weight: 700; color: #a855f7;">{{ $summary['sakit'] ?? 0 }}</div>
            <div style="font-size: 0.875rem; color: #9ca3af; margin-top: 0.25rem;">Sakit</div>
        </div>

        <div style="background: rgba(239, 68, 68, 0.1); border-radius: 0.75rem; padding: 1rem; text-align: center;">
            <div style="font-size: 1.875rem; font-weight: 700; color: #ef4444;">{{ $summary['alpha'] ?? 0 }}</div>
            <div style="font-size: 0.875rem; color: #9ca3af; margin-top: 0.25rem;">Alpha</div>
        </div>

        <div style="background: rgba(107, 114, 128, 0.1); border-radius: 0.75rem; padding: 1rem; text-align: center;">
            <div style="font-size: 1.875rem; font-weight: 700; color: #6b7280;">{{ $summary['libur'] ?? 0 }}</div>
            <div style="font-size: 0.875rem; color: #9ca3af; margin-top: 0.25rem;">Libur</div>
        </div>
    </div>

    {{-- Rekap Table --}}
    <x-filament::section>
        <x-slot name="heading">
            Rekap {{ $periode['bulan_nama'] ?? '' }} {{ $periode['tahun'] ?? '' }}
        </x-slot>

        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
            <thead>
                <tr style="background: rgba(0,0,0,0.05); border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #9ca3af; text-transform: uppercase; font-size: 0.75rem;">Tanggal</th>
                    <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #9ca3af; text-transform: uppercase; font-size: 0.75rem;">Hari</th>
                    <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #9ca3af; text-transform: uppercase; font-size: 0.75rem;">Masuk</th>
                    <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #9ca3af; text-transform: uppercase; font-size: 0.75rem;">Pulang</th>
                    <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #9ca3af; text-transform: uppercase; font-size: 0.75rem;">Status</th>
                    <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #9ca3af; text-transform: uppercase; font-size: 0.75rem;">Telat</th>
                    <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #9ca3af; text-transform: uppercase; font-size: 0.75rem;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekap as $row)
                    @php
                        $statusRaw = $row['status_raw'] ?? strtolower($row['status'] ?? '');
                        $isLibur = $statusRaw === 'libur';
                        $isAlpha = $statusRaw === 'alpha';
                        $bgColor = $isLibur ? 'rgba(107,114,128,0.1)' : ($isAlpha ? 'rgba(239,68,68,0.1)' : 'transparent');
                    @endphp
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); background: {{ $bgColor }};">
                        <td style="padding: 0.75rem 1rem; white-space: nowrap;">{{ $row['tanggal']->format('d/m/Y') }}</td>
                        <td style="padding: 0.75rem 1rem; white-space: nowrap; color: #9ca3af;">{{ $row['hari'] }}</td>
                        <td style="padding: 0.75rem 1rem; text-align: center; font-family: monospace;">
                            @if($row['jam_masuk'] && $row['jam_masuk'] !== '-')
                                {{ $row['jam_masuk'] }}
                            @else
                                <span style="color: #4b5563;">-</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: center; font-family: monospace;">
                            @if($row['jam_pulang'] && $row['jam_pulang'] !== '-')
                                {{ $row['jam_pulang'] }}
                            @else
                                <span style="color: #4b5563;">-</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: center;">
                            @php
                                $statusColor = match($statusRaw) {
                                    'hadir' => 'success',
                                    'telat' => 'warning',
                                    'izin' => 'info',
                                    'sakit' => 'primary',
                                    'alpha' => 'danger',
                                    'libur' => 'gray',
                                    default => 'gray',
                                };
                            @endphp
                            <x-filament::badge :color="$statusColor" size="sm">
                                {{ $row['status'] }}
                            </x-filament::badge>
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: center;">
                            @if(isset($row['telat_menit']) && $row['telat_menit'] > 0)
                                <span style="color: #eab308; font-weight: 500;">{{ $row['telat_menit'] }} mnt</span>
                            @else
                                <span style="color: #4b5563;">-</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem; color: #9ca3af;">
                            {{ ($row['keterangan'] ?? '-') !== '-' ? $row['keterangan'] : '' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 3rem 1rem; text-align: center; color: #6b7280;">
                            Tidak ada data absensi untuk periode ini
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-filament::section>
</x-filament-panels::page>