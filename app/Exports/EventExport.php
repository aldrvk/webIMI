<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $year;

    public function __construct($year)
    {
        $this->year = $year;
    }

    public function collection()
    {
        return Event::whereYear('event_date', $this->year)
            ->with('proposingClub')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Event',
            'Penyelenggara',
            'Tanggal',
            'Lokasi',
            'Kuota'
        ];
    }

    public function map($event): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $event->event_name,
            $event->proposingClub->nama_klub ?? '-',
            $event->event_date ? $event->event_date->format('d/m/Y') : '-',
            $event->location,
            $event->max_participants ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
