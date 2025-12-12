<?php

namespace App\Exports;

use App\Models\ClubDues;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IuranExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $year;

    public function __construct($year)
    {
        $this->year = $year;
    }

    public function collection()
    {
        return ClubDues::where('payment_year', $this->year)
            ->with('club')
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Klub',
            'Tahun',
            'Tanggal Bayar',
            'Jumlah (Rp)',
            'Status'
        ];
    }

    public function map($dues): array
    {
        static $no = 0;
        $no++;

        // Pastikan payment_date ada sebelum di-format
        $tanggalBayar = '-';
        if ($dues->payment_date) {
            try {
                $tanggalBayar = \Carbon\Carbon::parse($dues->payment_date)->format('d/m/Y');
            } catch (\Exception $e) {
                $tanggalBayar = '-';
            }
        }

        return [
            $no,
            $dues->club->nama_klub ?? 'Klub Tidak Ditemukan',
            $dues->payment_year ?? '-',
            $tanggalBayar,
            $dues->amount_paid ?? 0,
            $dues->status ?? 'Unknown'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 30,
            'C' => 10,
            'D' => 15,
            'E' => 15,
            'F' => 15,
        ];
    }
}
