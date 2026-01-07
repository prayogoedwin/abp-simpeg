<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <x-filament::section class="text-center">
            <div class="text-2xl font-bold text-success-600">{{ $summary['hadir'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Hadir</div>
        </x-filament::section>

        <x-filament::section class="text-center">
            <div class="text-2xl font-bold text-warning-600">{{ $summary['telat'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Telat</div>
        </x-filament::section>

        <x-filament::section class="text-center">
            <div class="text-2xl font-bold text-info-600">{{ $summary['izin'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Izin</div>
        </x-filament::section>

        <x-filament::section class="text-center">
            <div class="text-2xl font-bold text-primary-600">{{ $summary['sakit'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Sakit</div>
        </x-filament::section>

        <x-filament::section class="text-center">
            <div class="text-2xl font-bold text-danger-600">{{ $summary['alpha'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Alpha</div>
        </x-filament::section>

        <x-filament::section class="text-center">
            <div class="text-2xl font-bold text-gray-600">{{ $summary['libur'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Libur</div>
        </x-filament::section>
    </div>

    {{-- Rekap Table --}}
    <x-filament::section>
        <x-slot name="heading">
            Rekap {{ $periode['bulan_nama'] }} {{ $periode['tahun'] }}
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800">
                        <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Tanggal</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Hari</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300">Jam Masuk</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300">Jam Pulang</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300">Telat</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($rekap as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 
                            @if($row['status_raw'] === 'libur') bg-gray-100 dark:bg-gray-800/30 @endif
                            @if($row['status_raw'] === 'alpha') bg-danger-50 dark:bg-danger-900/20 @endif
                        ">
                            <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                {{ $row['tanggal']->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $row['hari'] }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($row['jam_masuk'] && $row['jam_masuk'] !== '-')
                                    <span class="font-mono text-gray-900 dark:text-gray-100">{{ $row['jam_masuk'] }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($row['jam_pulang'] && $row['jam_pulang'] !== '-')
                                    <span class="font-mono text-gray-900 dark:text-gray-100">{{ $row['jam_pulang'] }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $statusColor = match($row['status_raw'] ?? $row['status']) {
                                        'hadir' => 'success',
                                        'telat' => 'warning',
                                        'izin' => 'info',
                                        'sakit' => 'primary',
                                        'alpha' => 'danger',
                                        'libur' => 'gray',
                                        default => 'gray',
                                    };
                                @endphp
                                <x-filament::badge :color="$statusColor">
                                    {{ $row['status'] }}
                                </x-filament::badge>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if(isset($row['telat_menit']) && $row['telat_menit'] > 0)
                                    <span class="text-warning-600 font-medium">{{ $row['telat_menit'] }} menit</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $row['keterangan'] !== '-' ? $row['keterangan'] : '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <x-heroicon-o-calendar-days class="w-12 h-12 text-gray-300 dark:text-gray-600" />
                                    <p>Tidak ada data absensi untuk periode ini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

    {{-- Detail Keterlambatan --}}
    @if(isset($summary['total_telat_menit']) && $summary['total_telat_menit'] > 0)
        <x-filament::section>
            <x-slot name="heading">
                <span class="flex items-center gap-2">
                    <x-heroicon-o-clock class="w-5 h-5 text-warning-500" />
                    Detail Keterlambatan
                </span>
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="p-4 bg-warning-50 dark:bg-warning-900/20 rounded-lg">
                    <div class="text-sm text-warning-700 dark:text-warning-400">Total Keterlambatan</div>
                    <div class="text-xl font-bold text-warning-600">
                        @php
                            $totalMenit = $summary['total_telat_menit'];
                            $jam = floor($totalMenit / 60);
                            $menit = $totalMenit % 60;
                        @endphp
                        @if($jam > 0)
                            {{ $jam }} jam {{ $menit }} menit
                        @else
                            {{ $menit }} menit
                        @endif
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Rata-rata Telat</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100">
                        @if($summary['telat'] > 0)
                            {{ round($summary['total_telat_menit'] / $summary['telat']) }} menit
                        @else
                            0 menit
                        @endif
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Frekuensi Telat</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $summary['telat'] ?? 0 }}x
                    </div>
                </div>
            </div>
        </x-filament::section>
    @endif
</div>