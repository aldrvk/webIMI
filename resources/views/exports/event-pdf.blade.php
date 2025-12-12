<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Event IMI {{ $year === 'overall' ? 'Overall' : 'Tahun ' . $year }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 5px; text-align: left; }
        th { background-color: #8E44AD; color: white; font-weight: bold; }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 5px; }
        .header { text-align: center; margin-bottom: 15px; }
        .footer { margin-top: 20px; text-align: right; font-size: 8px; color: #666; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN DATA EVENT IMI SUMATERA UTARA</h2>
        <p><strong>Periode: {{ $year === 'overall' ? 'Semua Tahun' : 'Tahun ' . $year }}</strong></p>
        <p style="font-size: 8px;">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 25%;">Nama Event</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 18%;">Lokasi</th>
                <th style="width: 12%;">Biaya Daftar</th>
                <th style="width: 15%;">Penyelenggara</th>
                <th style="width: 7%;">Peserta</th>
                <th style="width: 10%;">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @php $totalRevenue = 0; $totalPeserta = 0; @endphp
            @forelse($events as $index => $e)
            @php 
                $totalRevenue += $e->total_revenue; 
                $totalPeserta += $e->total_peserta;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $e->event_name }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($e->event_date)->format('d/m/Y') }}</td>
                <td>{{ $e->location }}</td>
                <td class="text-right">Rp {{ number_format($e->biaya_pendaftaran, 0, ',', '.') }}</td>
                <td>{{ $e->penyelenggara ?? '-' }}</td>
                <td class="text-center"><strong>{{ $e->total_peserta }}</strong></td>
                <td class="text-right"><strong>Rp {{ number_format($e->total_revenue, 0, ',', '.') }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px;">Tidak ada data event untuk periode ini.</td>
            </tr>
            @endforelse
            @if($events->count() > 0)
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td colspan="6" class="text-right">TOTAL:</td>
                <td class="text-center">{{ $totalPeserta }}</td>
                <td class="text-right">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Total: <strong>{{ $events->count() }}</strong> event | Total Revenue: <strong>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong></p>
        <p>IMI Sumatera Utara - Jl. Taruma No. 52 Medan</p>
    </div>
</body>
</html>