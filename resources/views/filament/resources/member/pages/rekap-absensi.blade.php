<x-filament-panels::page>
    {{-- Filter Form --}}
    <div class="mb-6">
        {{ $this->form }}
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4">
            <div class="text-center">
                <div class="text-3xl font-bold text-success-600 dark:text-success-400">
                    {{ $summary['hadir'] ?? 0 }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Hadir</div>
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4">
            <div class="text-center">
                <div class="text-3xl font-bold text-warning-600 dark:text-warning-400">
                    {{ $summary['telat'] ?? 0 }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Telat</div>
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4">
            <div class="text-center">
                <div class="text-3xl font-bold text-info-600 dark:text-info-400">
                    {{ $summary['izin'] ?? 0 }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Izin</div>
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4">
            <div class="text-center">
                <div class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                    {{ $summary['sakit'] ?? 0 }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sakit</div>
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4">
            <div class="text-center">
                <div class="text-3xl font-bold text-danger-600 dark:text-danger-400">
                    {{ $summary['alpha'] ?? 0 }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Alpha</div>
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4">
            <div class="text-center">
                <div class="text-3xl font-bold text-gray-600 dark:text-gray-400">
                    {{ $summary['libur'] ?? 0 }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Libur</div>
            </div>
        </div>
    </div>

    {{-- Rekap Table --}}
    <x-filament::section>
        <x-slot name="heading">
            Rekap {{ $periode['bulan_nama'] ?? '' }} {{ $periode['tahun'] ?? '' }}
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                            Tanggal
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                            Hari
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                            Masuk
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                            Pulang
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                            Telat
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                            Keterangan
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @forelse($rekap as $row)
                        @php
                            $statusRaw = $row['status_raw'] ?? strtolower($row['status'] ?? '');
                            $isLibur = $statusRaw === 'libur';
                            $isAlpha = $statusRaw === 'alpha';
                        @endphp
                        <tr class="
                            {{ $isLibur ? 'bg-gray-50 dark:bg-gray-800/50' : '' }}
                            {{ $isAlpha ? 'bg-danger-50 dark:bg-danger-900/10' : '' }}
                            hover:bg-gray-50 dark:hover:bg-gray-800/30
                        ">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-900 dark:text-gray-100">
                                {{ $row['tanggal']->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                {{ $row['hari'] }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                @if($row['jam_masuk'] && $row['jam_masuk'] !== '-')
                                    <span class="font-mono text-gray-900 dark:text-gray-100">
                                        {{ $row['jam_masuk'] }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-600">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                @if($row['jam_pulang'] && $row['jam_pulang'] !== '-')
                                    <span class="font-mono text-gray-900 dark:text-gray-100">
                                        {{ $row['jam_pulang'] }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-600">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
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
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                @if(isset($row['telat_menit']) && $row['telat_menit'] > 0)
                                    <span class="text-warning-600 dark:text-warning-400 font-medium">
                                        {{ $row['telat_menit'] }} mnt
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-600">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ ($row['keterangan'] ?? '-') !== '-' ? $row['keterangan'] : '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-calendar-days class="w-12 h-12 text-gray-300 dark:text-gray-600" />
                                    <p class="text-sm">Tidak ada data absensi untuk periode ini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>