<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .info { margin-bottom: 15px; }
        .info td { padding: 3px 10px 3px 0; }
        table.rekap { width: 100%; border-collapse: collapse; }
        table.rekap th, table.rekap td { border: 1px solid #ddd; padding: 6px; text-align: center; }
        table.rekap th { background: #4F46E5; color: white; }
        .summary { margin-top: 20px; }
        .summary td { padding: 5px 15px 5px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h2>REKAP ABSENSI</h2>
        <p>Periode: {{ DateTime::createFromFormat('!m', $bulan)->format('F') }} {{ $tahun }}</p>
    </div>

    <table class="info">
        <tr>
            <td><strong>Nama</strong></td>
            <td>: {{ $member->name }}</td>
        </tr>
        <tr>
            <td><strong>No. Karyawan</strong></td>
            <td>: {{ $member->no_karyawan ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Instansi</strong></td>
            <td>: {{ $member->instansi?->nama ?? '-' }}</td>
        </tr>
    </table>

    <table class="rekap">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekap as $row)
                <tr>
                    <td>{{ $row['tanggal']->format('d/m/Y') }}</td>
                    <td>{{ $row['hari'] }}</td>
                    <td>{{ $row['jam_masuk'] }}</td>
                    <td>{{ $row['jam_pulang'] }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td style="text-align: left;">{{ $row['keterangan'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td><strong>Total Hadir:</strong> {{ $summary['total_hadir'] }}</td>
            <td><strong>Terlambat:</strong> {{ $summary['total_terlambat'] }}</td>
            <td><strong>Alpha:</strong> {{ $summary['total_alpha'] }}</td>
            <td><strong>Izin:</strong> {{ $summary['total_izin'] }}</td>
        </tr>
        <tr>
            <td><strong>Sakit:</strong> {{ $summary['total_sakit'] }}</td>
            <td><strong>Cuti:</strong> {{ $summary['total_cuti'] }}</td>
            <td><strong>Libur:</strong> {{ $summary['total_libur'] }}</td>
            <td></td>
        </tr>
    </table>
</body>
</html>