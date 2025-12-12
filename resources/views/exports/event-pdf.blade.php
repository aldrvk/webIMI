<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Event {{ $year }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        h1 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 18px;
        }

        .meta {
            text-align: center;
            margin-bottom: 20px;
            font-size: 10px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 10px;
        }

        .footer {
            margin-top: 20px;
            font-size: 9px;
            text-align: center;
            color: #999;
        }
    </style>
</head>

<body>
    <h1>LAPORAN EVENT IMI SUMUT {{ $year }}</h1>
    <div class="meta">
        <p><strong>Dicetak:</strong> {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y H:i') }} WIB</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Nama Event</th>
                <th width="25%">Penyelenggara</th>
                <th width="15%">Tanggal</th>
                <th width="20%">Lokasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($events as $index => $e)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $e->event_name }}</td>
                    <td>{{ $e->proposingClub->nama_klub ?? '-' }}</td>
                    <td>{{ $e->event_date ? $e->event_date->format('d/m/Y') : '-' }}</td>
                    <td>{{ $e->location }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini digenerate otomatis oleh Sistem Informasi IMI Sumut</p>
    </div>
</body>

</html>