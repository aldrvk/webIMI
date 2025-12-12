<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Iuran {{ $year }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11px; 
            margin: 20px;
        }
        h1 { 
            text-align: center; 
            margin-bottom: 5px; 
            font-size: 18px;
            color: #ca8a04;
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
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #ca8a04; 
            color: white;
            font-weight: bold; 
            font-size: 10px; 
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .amount { 
            text-align: right; 
            font-weight: bold;
        }
        .footer { 
            margin-top: 30px; 
            font-size: 9px; 
            text-align: center; 
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .header-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .status-approved {
            color: #15803d;
            font-weight: bold;
        }
        .status-pending {
            color: #ca8a04;
            font-weight: bold;
        }
        .status-rejected {
            color: #dc2626;
            font-weight: bold;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #fef3c7;
            border: 2px solid #ca8a04;
            border-radius: 5px;
        }
        tfoot td {
            background-color: #fef3c7;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header-logo">
        <h1>IKATAN MOTOR INDONESIA</h1>
        <p style="margin: 0; font-size: 12px; color: #666;">Pengurus Provinsi Sumatera Utara</p>
    </div>

    <h1>LAPORAN IURAN KLUB {{ $year }}</h1>
    <div class="meta">
        <p><strong>Dicetak:</strong> {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y H:i') }} WIB</p>
    </div>

    @if($iuran->count() > 0)
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Klub</th>
                <th width="10%">Tahun</th>
                <th width="15%">Tgl Bayar</th>
                <th width="18%">Jumlah</th>
                <th width="17%">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalIuran = 0;
                $totalApproved = 0;
            @endphp
            @foreach($iuran as $index => $i)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $i->club_name }}</td>
                <td style="text-align: center;">{{ $i->payment_year ?? '-' }}</td>
                <td>
                    @if($i->payment_date)
                        {{ \Carbon\Carbon::parse($i->payment_date)->format('d/m/Y') }}
                    @else
                        -
                    @endif
                </td>
                <td class="amount">Rp {{ number_format($i->amount_paid ?? 0, 0, ',', '.') }}</td>
                <td class="status-{{ strtolower($i->status ?? 'unknown') }}">{{ $i->status ?? 'Unknown' }}</td>
            </tr>
            @php
                $totalIuran += $i->amount_paid ?? 0;
                if (($i->status ?? '') === 'Approved') {
                    $totalApproved += $i->amount_paid ?? 0;
                }
            @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right;">TOTAL IURAN:</td>
                <td class="amount" style="color: #ca8a04;">Rp {{ number_format($totalIuran, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="summary">
        <p style="margin: 5px 0;"><strong>Total Klub:</strong> {{ $iuran->count() }} klub</p>
        <p style="margin: 5px 0;"><strong>Total Iuran Terkumpul:</strong> Rp {{ number_format($totalIuran, 0, ',', '.') }}</p>
        <p style="margin: 5px 0;"><strong>Total Iuran Terverifikasi:</strong> Rp {{ number_format($totalApproved, 0, ',', '.') }}</p>
    </div>
    @else
    <div style="text-align: center; padding: 40px; background-color: #f9fafb; border-radius: 8px; border: 1px dashed #ddd;">
        <p style="color: #666; font-size: 14px; margin: 0;">Tidak ada data iuran untuk tahun {{ $year }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Dokumen ini digenerate otomatis oleh Sistem Informasi IMI Sumut</p>
    </div>
</body>
</html>