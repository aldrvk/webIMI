<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class IuranExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $iuran;
    protected $year;

    public function __construct($iuran, $year)
    {
        $this->iuran = $iuran;
        $this->year = $year;
    }

    public function collection()
    {
        return $this->iuran;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Klub',
            'Tahun Pembayaran',
            'Tanggal Bayar',
            'Jumlah',
            'Status'
        ];
    }

    public function map($iuran): array
    {
        static $row = 0;
        $row++;
        
        return [
            $row,
            $iuran->nama_klub,
            $iuran->payment_year,
            \Carbon\Carbon::parse($iuran->payment_date)->format('d/m/Y'),
            'Rp ' . number_format($iuran->amount_paid, 0, ',', '.'),
            $iuran->status
        ];
    }

    public function title(): string
    {
        return $this->year === 'overall' 
            ? 'Data Iuran Overall' 
            : 'Data Iuran ' . $this->year;
    }
}
