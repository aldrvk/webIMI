<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PembalapExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $year;

    public function __construct($year)
    {
        $this->year = $year;
    }

    public function collection()
    {
        return User::where('role', 'pembalap')
            ->with(['profile.club', 'profile.kisLicense'])
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Email',
            'Klub',
            'No. KIS',
            'Status',
            'Tanggal Daftar'
        ];
    }

    public function map($user): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $user->name,
            $user->email,
            optional($user->profile)->club->nama_klub ?? '-',
            optional(optional($user->profile)->kisLicense)->kis_number ?? '-',
            ucfirst($user->status),
            $user->created_at->format('d/m/Y')
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
            'B' => 25,
            'C' => 30,
            'D' => 25,
            'E' => 15,
            'F' => 12,
            'G' => 15,
        ];
    }
}
