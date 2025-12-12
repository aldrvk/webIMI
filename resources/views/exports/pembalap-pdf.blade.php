<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Pembalap IMI {{ $year === 'overall' ? 'Overall' : 'Tahun ' . $year }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 5px; text-align: left; }
        th { background-color: #4472C4; color: white; font-weight: bold; font-size: 9px; }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 5px; }
        .header { text-align: center; margin-bottom: 15px; }
        .status-aktif { color: green; font-weight: bold; }
        .status-expired { color: red; font-weight: bold; }
        .status-none { color: gray; font-style: italic; }
        .footer { margin-top: 20px; text-align: right; font-size: 8px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN DATA PEMBALAP IMI SUMATERA UTARA</h2>
        <p><strong>Periode: {{ $year === 'overall' ? 'Semua Tahun' : 'Tahun ' . $year }}</strong></p>
        <p style="font-size: 8px;">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 18%;">Nama</th>
                <th style="width: 15%;">Email</th>
                <th style="width: 12%;">Klub</th>
                <th style="width: 10%;">No. Telp</th>
                <th style="width: 12%;">No. KIS</th>
                <th style="width: 12%;">Kategori</th>
                <th style="width: 8%;">Tgl Daftar</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pembalap as $index => $p)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $p->nama }}</td>
                <td style="font-size: 8px;">{{ $p->email }}</td>
                <td>{{ $p->nama_klub }}</td>
                <td>{{ $p->phone_number ?? '-' }}</td>
                <td>{{ $p->kis_number ?? '-' }}</td>
                <td>{{ $p->nama_kategori ?? '-' }}</td>
                <td style="text-align: center;">{{ $p->issued_date ? \Carbon\Carbon::parse($p->issued_date)->format('d/m/Y') : '-' }}</td>
                <td class="{{ $p->status_kis === 'Aktif' ? 'status-aktif' : ($p->status_kis === 'Expired' ? 'status-expired' : 'status-none') }}">
                    {{ $p->status_kis }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">Tidak ada data pembalap untuk periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total: <strong>{{ $pembalap->count() }}</strong> pembalap</p>
        <p>IMI Sumatera Utara - Jl. Taruma No. 52 Medan</p>
    </div>
</body>
</html>