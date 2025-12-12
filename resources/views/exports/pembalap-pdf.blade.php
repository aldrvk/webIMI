<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Pembalap {{ $year }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 15px;
        }

        h1 {
            text-align: center;
            margin-bottom: 5px;
            font-size: 16px;
            color: #1e40af;
        }

        .meta {
            text-align: center;
            margin-bottom: 15px;
            font-size: 9px;
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
            background-color: #1e40af;
            color: white;
            font-weight: bold;
            font-size: 9px;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .footer {
            margin-top: 20px;
            font-size: 8px;
            text-align: center;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .header-logo {
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="header-logo">
        <h1>IKATAN MOTOR INDONESIA</h1>
        <p style="margin: 0; font-size: 11px; color: #666;">Pengurus Provinsi Sumatera Utara</p>
    </div>

    <h1>LAPORAN DATA PEMBALAP</h1>
    <div class="meta">
        <p><strong>Tahun:</strong> {{ $year }} | <strong>Dicetak:</strong>
            {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y H:i') }} WIB</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="20%">Nama</th>
                <th width="22%">Email</th>
                <th width="18%">Klub</th>
                <th width="13%">No. KIS</th>
                <th width="10%">Status</th>
                <th width="13%">Tgl Daftar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pembalap as $index => $p)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->email }}</td>
                    <td>{{ optional($p->profile)->club->nama_klub ?? '-' }}</td>
                    <td>{{ optional(optional($p->profile)->kisLicense)->kis_number ?? '-' }}</td>
                    <td>{{ ucfirst($p->status) }}</td>
                    <td>{{ $p->created_at->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data pembalap</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Total Pembalap:</strong> {{ $pembalap->count() }} orang</p>
        <p>Dokumen ini digenerate otomatis oleh Sistem Informasi IMI Sumut</p>
    </div>
</body>

</html>