<x-filament-panels::page>
    <form wire:submit.prevent="">
        {{ $this->form }}
    </form>

    {{-- List Pegawai --}}
    @if($this->instansi_id && !$this->member_id)
        <x-filament::section>
            <x-slot name="heading">Daftar Pegawai</x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($this->members as $member)
                    <div 
                        wire:click="$set('member_id', {{ $member['id'] }})"
                        class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-primary-500 hover:shadow-md transition"
                    >
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $member['name'] }}</div>
                        <div class="text-sm text-gray-500">{{ $member['no_karyawan'] ?? '-' }}</div>
                    </div>
                @empty
                    <div class="col-span-full text-center text-gray-500 py-8">
                        Tidak ada pegawai di instansi ini
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    @endif

    {{-- Rekap Absensi --}}
    @if($this->member_id && count($this->rekapData) > 0)
        {{-- Info Pegawai --}}
        <x-filament::section>
            <x-slot name="heading">Informasi Pegawai</x-slot>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-sm text-gray-500">Nama</div>
                    <div class="font-semibold">{{ $this->selectedMember?->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">No. Karyawan</div>
                    <div class="font-semibold">{{ $this->selectedMember?->no_karyawan ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Instansi</div>
                    <div class="font-semibold">{{ $this->selectedMember?->instansi?->nama ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Periode</div>
                    <div class="font-semibold">
                        {{ DateTime::createFromFormat('!m', $this->bulan)->format('F') }} {{ $this->tahun }}
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Summary --}}
        <x-filament::section>
            <x-slot name="heading">Ringkasan</x-slot>
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">{{ $this->summaryData['total_hadir'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Hadir</div>
                </div>
                <div class="text-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600">{{ $this->summaryData['total_terlambat'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Terlambat</div>
                </div>
                <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-red-600">{{ $this->summaryData['total_alpha'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Alpha</div>
                </div>
                <div class="text-center p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-orange-600">{{ $this->summaryData['total_izin'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Izin</div>
                </div>
                <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">{{ $this->summaryData['total_sakit'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Sakit</div>
                </div>
                <div class="text-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600">{{ $this->summaryData['total_cuti'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Cuti</div>
                </div>
                <div class="text-center p-3 bg-gray-50 dark:bg-gray-900/20 rounded-lg">
                    <div class="text-2xl font-bold text-gray-600">{{ $this->summaryData['total_libur'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Libur</div>
                </div>
            </div>
        </x-filament::section>

        {{-- Tabel Rekap --}}
        <x-filament::section>
            <x-slot name="heading">Detail Absensi</x-slot>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
                            <th class="px-4 py-3 text-left font-semibold">Hari</th>
                            <th class="px-4 py-3 text-center font-semibold">Jam Masuk</th>
                            <th class="px-4 py-3 text-center font-semibold">Jam Pulang</th>
                            <th class="px-4 py-3 text-center font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($this->rekapData as $row)
                            <tr class="{{ $row['status_raw'] ? '' : 'bg-gray-50 dark:bg-gray-900/50' }}">
                                <td class="px-4 py-3">{{ $row['tanggal']->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">{{ $row['hari'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $row['jam_masuk'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $row['jam_pulang'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($row['status_raw'])
                                        <x-filament::badge 
                                            :color="App\Models\Absensi::STATUS_COLORS[$row['status_raw']] ?? 'gray'"
                                        >
                                            {{ $row['status'] }}
                                        </x-filament::badge>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $row['keterangan'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>