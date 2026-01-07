<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi - {{ $periode['instansi'] ?? '' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 14px;
            color: #4F46E5;
            margin-bottom: 3px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #4F46E5;
            color: white;
            padding: 4px 2px;
            text-align: center;
            font-size: 7px;
            font-weight: bold;
        }
        th.nama {
            text-align: left;
            min-width: 100px;
        }
        th.weekend {
            background: #6366f1;
        }
        td {
            padding: 3px 2px;
            text-align: center;
            border-bottom: 1px solid #eee;
            font-size: 7px;
        }
        td.nama {
            text-align: left;
            font-weight: 500;
        }
        td.hadir { background: rgba(34, 197, 94, 0.15); }
        td.telat { background: rgba(234, 179, 8, 0.15); }
        td.izin { background: rgba(59, 130, 246, 0.15); }
        td.sakit { background: rgba(168, 85, 247, 0.15); }
        td.alpha { background: rgba(239, 68, 68, 0.15); }
        td.weekend { background: rgba(107, 114, 128, 0.15); }
        .jam-masuk { color: #22c55e; }
        .jam-pulang { color: #ef4444; }
        .footer {
            margin-top: 15px;
            font-size: 8px;
            color: #666;
            text-align: right;
        }
        .legend {
            margin-top: 10px;
            display: flex;
            gap: 15px;
            font-size: 7px;
        }
        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }
        .legend-color {
            width: 10px;
            height: 10px;
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKAP ABSENSI</h1>
        <p>{{ $periode['instansi'] ?? '-' }} - {{ $periode['bulan_nama'] }} {{ $periode['tahun'] }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="nama">Nama</th>
                @foreach($tanggalList as $tgl)
                    <th class="{{ $tgl['is_weekend'] ? 'weekend' : '' }}">
                        {{ $tgl['tanggal'] }}<br>
                        <span style="font-weight: normal;">{{ $tgl['hari'] }}</span>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rekapData as $row)
                <tr>
                    <td class="nama">{{ $row['member']->name }}</td>
                    @foreach($tanggalList as $tgl)
                        @php
                            $absen = $row['absensi'][$tgl['tanggal']] ?? null;
                            $cellClass = '';
                            
                            if ($tgl['is_weekend']) {
                                $cellClass = 'weekend';
                            } elseif ($absen && $absen['status']) {
                                $cellClass = $absen['status'];
                            }
                        @endphp
                        <td class="{{ $cellClass }}">
                            @if($absen && $absen['jam_masuk'])
                                <span class="jam-masuk">{{ $absen['jam_masuk'] }}</span><br>
                                <span class="jam-pulang">{{ $absen['jam_pulang'] }}</span>
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
    </div>
</body>
</html>