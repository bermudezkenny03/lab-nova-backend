<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserActivityExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(
        private string $startDate,
        private string $endDate
    ) {}

    public function collection()
    {
        return User::withCount([
            'reservations' => fn($q) => $q->whereBetween('start_time', [$this->startDate, $this->endDate])
        ])->get()->map(fn($u) => [
            'User'               => trim(($u->name ?? '') . ' ' . ($u->last_name ?? '')),
            'Email'              => $u->email,
            'Total Reservations' => $u->reservations_count,
        ]);
    }

    public function headings(): array
    {
        return ['User', 'Email', 'Total Reservations'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
