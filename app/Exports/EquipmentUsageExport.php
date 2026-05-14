<?php

namespace App\Exports;

use App\Models\Equipment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EquipmentUsageExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(
        private string $startDate,
        private string $endDate
    ) {}

    public function collection()
    {
        return Equipment::withCount([
            'reservations' => fn($q) => $q->whereBetween('start_time', [$this->startDate, $this->endDate])
        ])->get()->map(fn($eq) => [
            'Equipment'          => $eq->name,
            'Total Reservations' => $eq->reservations_count,
        ]);
    }

    public function headings(): array
    {
        return ['Equipment', 'Total Reservations'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
