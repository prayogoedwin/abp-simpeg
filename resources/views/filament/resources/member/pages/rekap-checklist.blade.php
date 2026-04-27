<x-filament-panels::page>
    {{-- Filter Form --}}
    {{ $this->form }}



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
                    <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #9ca3af; text-transform: uppercase; font-size: 0.75rem;">Template yang digunakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekap as $row)

                <tr
                    x-data
                    @click="{{ $row['checklist_id'] ? "window.location.href = '" . route('filament.backend.resources.checklists.view', $row['checklist_id']) . "'" : "" }}"
                    style="{{ $row['checklist_id'] ? 'cursor: pointer;' : '' }} border-bottom: 1px solid rgba(255,255,255,0.05);"
                    class="{{ $row['checklist_id'] ? 'hover:bg-white/5' : '' }}">
                    <td style="padding: 0.75rem 1rem;">{{ $row['tanggal']->format('d/m/Y') }}</td>
                    <td style="padding: 0.75rem 1rem;">{{ $row['hari'] }}</td>
                    <td style="padding: 0.75rem 1rem;">{{ $row['nama_template'] ?? '-' }}</td>
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