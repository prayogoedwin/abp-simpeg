<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Checklist - {{ $periode['instansi'] ?? '' }}</title>
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
            font-size: 7px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 8px;
        }
        .header h1 {
            font-size: 12px;
            color: #4F46E5;
            margin-bottom: 2px;
        }
        .header p {
            font-size: 9px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #4F46E5;
            color: white;
            padding: 3px 1px;
            text-align: center;
            font-size: 6px;
            font-weight: bold;
        }
        th.nama {
            text-align: left;
            min-width: 80px;
            padding-left: 3px;
        }
        th.weekend {
            background: #6366f1;
        }
        td {
            padding: 2px 1px;
            text-align: center;
            border-bottom: 1px solid #eee;
            font-size: 6px;
        }
        td.nama {
            text-align: left;
            font-weight: 500;
            padding-left: 3px;
            white-space: nowrap;
            overflow: hidden;
            max-width: 80px;
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
            margin-top: 10px;
            font-size: 7px;
            color: #666;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKAP CHECKLIST</h1>
        <p>{{ $periode['instansi'] ?? '-' }} - {{ $periode['bulan_nama'] }} {{ $periode['tahun'] }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="nama">Nama</th>
                @foreach($tanggalList as $tgl)
                    <th class="{{ $tgl['is_weekend'] ? 'weekend' : '' }}">
                        {{ $tgl['tanggal'] }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rekapData as $row)
                <tr>
                    <td class="nama">{{ \Illuminate\Support\Str::limit($row['nama'], 15) }}</td>
                    @foreach($tanggalList as $tgl)
                        @php
                            $checklist = $row['checklist'][$tgl['tanggal']] ?? null;
                            $cellClass = '';
                            
                            if ($tgl['is_weekend']) {
                                $cellClass = 'weekend';
                            } elseif ($checklist && $checklist['isAny']) {
                                $cellClass = 'hadir';
                            }
                        @endphp
                        <td class="{{ $cellClass }}">
                            @if($checklist && $checklist['isAny'])
                                <span>Ada Checklist</span>
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
        Dicetak: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>