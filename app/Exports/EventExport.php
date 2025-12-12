<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected $events;
    protected $year;

    public function __construct($events, $year)
    {
        $this->events = $events;
        $this->year = $year;
    }

    public function collection()
    {
        return $this->events;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Event',
            'Tanggal',
            'Lokasi',
            'Biaya Pendaftaran',
            'Penyelenggara',
            'Total Peserta',
            'Total Revenue'
        ];
    }

    public function map($event): array
    {
        static $row = 0;
        $row++;
        
        return [
            $row,
            $event->event_name,
            \Carbon\Carbon::parse($event->event_date)->format('d/m/Y'),
            $event->location,
            $event->biaya_pendaftaran,
            $event->penyelenggara ?? '-',
            $event->total_peserta,
            $event->total_revenue
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '8E44AD']]],
        ];
    }

    public function title(): string
    {
        return $this->year === 'overall' 
            ? 'Data Event Overall' 
            : 'Data Event ' . $this->year;
    }
}
