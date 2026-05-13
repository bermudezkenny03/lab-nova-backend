<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\ReservationStatus;
use App\Models\ReportRequest;
use App\Models\ReportRequestStatus;
use App\Models\ReportRequestType;
use App\Models\Report;
use App\Models\ReportFileType;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::limit(5)->get();
        $equipments = Equipment::limit(10)->get();
        $reservationStatuses = ReservationStatus::all();
        $reportRequestTypes = ReportRequestType::all();
        $reportRequestStatuses = ReportRequestStatus::all();
        $reportFileTypes = ReportFileType::all();

        if ($users->isEmpty() || $equipments->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios o equipos. Ejecuta UserSeeder y CategorySeeder primero.');
            return;
        }

        // Seed Reservations
        $this->seedReservations($users, $equipments, $reservationStatuses);

        // Seed Report Requests & Reports
        $this->seedReports($users, $reportRequestTypes, $reportRequestStatuses, $reportFileTypes);

        $this->command->info('✅ Datos de prueba creados correctamente');
    }

    private function seedReservations($users, $equipments, $statuses): void
    {
        $statusSlugs = ['pending', 'approved', 'rejected', 'completed'];

        for ($i = 0; $i < 20; $i++) {
            $statusSlug = $statusSlugs[array_rand($statusSlugs)];
            $status = $statuses->firstWhere('slug', $statusSlug);
            $user = $users->random();
            $equipment = $equipments->random();

            $startTime = now()->subDays(rand(0, 60))->subHours(rand(0, 23));
            $endTime = $startTime->copy()->addHours(rand(1, 4));

            Reservation::create([
                'user_id' => $user->id,
                'equipment_id' => $equipment->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'reservation_status_id' => $status->id,
                'notes' => rand(0, 1) ? 'Reserva de prueba' : null,
                'approved_by' => in_array($statusSlug, ['approved', 'rejected']) ? $users->random()->id : null,
                'approved_at' => in_array($statusSlug, ['approved', 'rejected']) ? now()->subDays(rand(0, 5)) : null,
            ]);
        }

        $this->command->info('   📅 20 reservas creadas');
    }

    private function seedReports($users, $types, $statuses, $fileTypes): void
    {
        for ($i = 0; $i < 10; $i++) {
            $type = $types->random();
            $status = $statuses->random();
            $user = $users->random();

            $reportRequest = ReportRequest::create([
                'user_id' => $user->id,
                'report_request_type_id' => $type->id,
                'start_date' => now()->subDays(rand(10, 30)),
                'end_date' => now()->subDays(rand(0, 9)),
                'report_request_status_id' => $status->id,
                'filters' => json_encode(['example' => 'filter']),
            ]);

            if ($status->slug === 'completed') {
                $fileType = $fileTypes->random();
                Report::create([
                    'report_request_id' => $reportRequest->id,
                    'file_path' => "/reports/sample_{$i}.{$fileType->extension}",
                    'file_name' => "reporte_{$i}.{$fileType->extension}",
                    'report_file_type_id' => $fileType->id,
                    'generated_at' => now()->subDays(rand(0, 5)),
                ]);
            }
        }

        $this->command->info('   📊 10 solicitudes de reporte creadas');
    }
}
