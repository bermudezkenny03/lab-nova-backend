<?php

namespace App\Exports;

use App\Models\Reservation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReservationsExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(
        private string $startDate,
        private string $endDate
    ) {}

    public function collection()
    {
        return Reservation::with(['user', 'equipment', 'reservation_status'])
            ->whereBetween('start_time', [$this->startDate, $this->endDate])
            ->orderBy('start_time')
            ->get()
            ->map(fn($r) => [
                'ID'         => $r->id,
                'User'       => trim(($r->user->name ?? '') . ' ' . ($r->user->last_name ?? '')),
                'Equipment'  => $r->equipment->name ?? '',
                'Start Time' => $r->start_time,
                'End Time'   => $r->end_time,
                'Status'     => $r->reservation_status->name ?? '',
                'Notes'      => $r->notes ?? '',
            ]);
    }

    public function headings(): array
    {
        return ['ID', 'User', 'Equipment', 'Start Time', 'End Time', 'Status', 'Notes'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
