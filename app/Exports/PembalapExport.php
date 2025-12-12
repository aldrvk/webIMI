<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PembalapExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected $pembalap;
    protected $year;

    public function __construct($pembalap, $year)
    {
        $this->pembalap = $pembalap;
        $this->year = $year;
    }

    public function collection()
    {
        return $this->pembalap;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Pembalap',
            'Email',
            'Klub',
            'Telepon',
            'No. KIS',
            'Kategori',
            'Tanggal Daftar',
            'Tanggal Expired',
            'Status'
        ];
    }

    public function map($pembalap): array
    {
        static $row = 0;
        $row++;
        
        return [
            $row,
            $pembalap->nama,
            $pembalap->email,
            $pembalap->nama_klub,
            $pembalap->phone_number ?? '-',
            $pembalap->kis_number ?? '-',
            $pembalap->nama_kategori ?? '-',
            $pembalap->issued_date ? \Carbon\Carbon::parse($pembalap->issued_date)->format('d/m/Y') : '-',
            $pembalap->expiry_date ? \Carbon\Carbon::parse($pembalap->expiry_date)->format('d/m/Y') : '-',
            $pembalap->status_kis
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '4472C4']]],
        ];
    }

    public function title(): string
    {
        return $this->year === 'overall' 
            ? 'Data Pembalap Overall' 
            : 'Data Pembalap ' . $this->year;
    }
}
