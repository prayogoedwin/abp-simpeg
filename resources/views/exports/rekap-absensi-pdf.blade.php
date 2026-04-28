<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi - {{ $member->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 18px;
            color: #4F46E5;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 12px;
            color: #666;
        }
        .info {
            margin-bottom: 20px;
        }
        .info table {
            width: 100%;
        }
        .info td {
            padding: 3px 0;
        }
        .info td:first-child {
            width: 100px;
            font-weight: bold;
        }
        .summary {
            display: flex;
            margin-bottom: 20px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-table td {
            padding: 8px 15px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .summary-table .label {
            font-size: 10px;
            color: #666;
        }
        .summary-table .value {
            font-size: 18px;
            font-weight: bold;
        }
        .summary-table .hadir { color: #22c55e; background: rgba(34, 197, 94, 0.1); }
        .summary-table .telat { color: #eab308; background: rgba(234, 179, 8, 0.1); }
        .summary-table .izin { color: #3b82f6; background: rgba(59, 130, 246, 0.1); }
        .summary-table .sakit { color: #a855f7; background: rgba(168, 85, 247, 0.1); }
        .summary-table .alpha { color: #ef4444; background: rgba(239, 68, 68, 0.1); }
        .summary-table .libur { color: #6b7280; background: rgba(107, 114, 128, 0.1); }
        
        .rekap-table {
            width: 100%;
            border-collapse: collapse;
        }
        .rekap-table th {
            background: #4F46E5;
            color: white;
            padding: 8px 5px;
            text-align: center;
            font-size: 10px;
            text-transform: uppercase;
        }
        .rekap-table td {
            padding: 6px 5px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }
        .rekap-table td:first-child,
        .rekap-table td:nth-child(2) {
            text-align: left;
        }
        .rekap-table td:last-child {
            text-align: left;
        }
        .rekap-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .rekap-table tr.libur {
            background: rgba(107, 114, 128, 0.1);
        }
        .rekap-table tr.alpha {
            background: rgba(239, 68, 68, 0.1);
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-hadir { background: #dcfce7; color: #166534; }
        .badge-telat { background: #fef9c3; color: #854d0e; }
        .badge-izin { background: #dbeafe; color: #1e40af; }
        .badge-sakit { background: #f3e8ff; color: #6b21a8; }
        .badge-alpha { background: #fee2e2; color: #991b1b; }
        .badge-libur { background: #f3f4f6; color: #374151; }
        
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        .telat-value {
            color: #eab308;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKAP ABSENSI</h1>
        <p>{{ $periode['bulan_nama'] }} {{ $periode['tahun'] }}</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td>Nama</td>
                <td>: {{ $member->name }}</td>
            </tr>
            <tr>
                <td>No. Karyawan</td>
                <td>: {{ $member->no_karyawan ?? '-' }}</td>
            </tr>
            <tr>
                <td>Instansi</td>
                <td>: {{ $member->instansi->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td>Posisi</td>
                <td>: {{ $member->posisi->nama ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="summary-table">
        <tr>
            <td class="hadir">
                <div class="value">{{ $summary['total_hadir'] ?? 0 }}</div>
                <div class="label">Hadir</div>
            </td>
            <td class="telat">
                <div class="value">{{ $summary['total_terlambat'] ?? 0 }}</div>
                <div class="label">Telat</div>
            </td>
            <td class="izin">
                <div class="value">{{ $summary['total_izin'] ?? 0 }}</div>
                <div class="label">Izin</div>
            </td>
            <td class="sakit">
                <div class="value">{{ $summary['total_sakit'] ?? 0 }}</div>
                <div class="label">Sakit</div>
            </td>
            <td class="alpha">
                <div class="value">{{ $summary['total_alpha'] ?? 0 }}</div>
                <div class="label">Alpha</div>
            </td>
            <td class="libur">
                <div class="value">{{ $summary['total_libur'] ?? 0 }}</div>
                <div class="label">Libur</div>
            </td>
        </tr>
    </table>

    <table class="rekap-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Masuk</th>
                <th>Pulang</th>
                <th>Status</th>
                <th>Telat</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekap as $row)
                @php
                    $statusRaw = $row['status_raw'] ?? strtolower($row['status'] ?? '');
                    $rowClass = $statusRaw === 'libur' ? 'libur' : ($statusRaw === 'alpha' ? 'alpha' : '');
                @endphp
                <tr class="{{ $rowClass }}">
                    <td>{{ $row['tanggal']->format('d/m/Y') }}</td>
                    <td>{{ $row['hari'] }}</td>
                    <td>{{ $row['jam_masuk'] !== '-' ? $row['jam_masuk'] : '-' }}</td>
                    <td>{{ $row['jam_pulang'] !== '-' ? $row['jam_pulang'] : '-' }}</td>
                    <td>
                        <span class="badge badge-{{ $statusRaw }}">{{ $row['status'] }}</span>
                    </td>
                    <td>
                        @if(isset($row['telat_menit']) && $row['telat_menit'] > 0)
                            <span class="telat-value">{{ $row['telat_menit'] }} mnt</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ ($row['keterangan'] ?? '-') !== '-' ? $row['keterangan'] : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
    </div>
</body>
</html>