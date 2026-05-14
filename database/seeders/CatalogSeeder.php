<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTable('equipment_statuses', [
            ['name' => 'Disponible', 'slug' => 'available', 'description' => 'Equipo disponible', 'sort_order' => 1],
            ['name' => 'Mantenimiento', 'slug' => 'maintenance', 'description' => 'Equipo en mantenimiento', 'sort_order' => 2],
            ['name' => 'Fuera de servicio', 'slug' => 'out_of_service', 'description' => 'Equipo no disponible', 'sort_order' => 3],
        ]);

        $this->seedTable('reservation_statuses', [
            ['name' => 'Pendiente', 'slug' => 'pending', 'description' => 'Reserva pendiente de aprobación', 'sort_order' => 1],
            ['name' => 'Aprobada', 'slug' => 'approved', 'description' => 'Reserva aprobada', 'sort_order' => 2],
            ['name' => 'Rechazada', 'slug' => 'rejected', 'description' => 'Reserva rechazada', 'sort_order' => 3],
            ['name' => 'Cancelada', 'slug' => 'cancelled', 'description' => 'Reserva cancelada', 'sort_order' => 4],
            ['name' => 'Completada', 'slug' => 'completed', 'description' => 'Reserva completada', 'sort_order' => 5],
        ]);

        $this->seedTable('report_request_statuses', [
            ['name' => 'Pendiente', 'slug' => 'pending', 'description' => 'Solicitud pendiente', 'sort_order' => 1],
            ['name' => 'Procesando', 'slug' => 'processing', 'description' => 'Solicitud en proceso', 'sort_order' => 2],
            ['name' => 'Completado', 'slug' => 'completed', 'description' => 'Solicitud completada', 'sort_order' => 3],
            ['name' => 'Fallido', 'slug' => 'failed', 'description' => 'Solicitud fallida', 'sort_order' => 4],
        ]);

        $this->seedTable('report_request_types', [
            ['name' => 'Reservaciones', 'slug' => 'reservations', 'description' => 'Reporte de reservaciones', 'sort_order' => 1],
            ['name' => 'Uso de equipos', 'slug' => 'equipment_usage', 'description' => 'Reporte de uso de equipos', 'sort_order' => 2],
            ['name' => 'Actividad de usuarios', 'slug' => 'user_activity', 'description' => 'Reporte de actividad de usuarios', 'sort_order' => 3],
        ]);

        $this->seedTable('report_file_types', [
            ['name' => 'PDF', 'slug' => 'pdf', 'extension' => 'pdf', 'sort_order' => 1],
            ['name' => 'Excel', 'slug' => 'xlsx', 'extension' => 'xlsx', 'sort_order' => 2],
            ['name' => 'Word', 'slug' => 'docx', 'extension' => 'docx', 'sort_order' => 3],
            ['name' => 'CSV', 'slug' => 'csv', 'extension' => 'csv', 'sort_order' => 4],
        ]);

        $this->seedTable('reservation_log_actions', [
            ['name' => 'Creada', 'slug' => 'created', 'description' => 'Reserva creada', 'sort_order' => 1],
            ['name' => 'Actualizada', 'slug' => 'updated', 'description' => 'Reserva actualizada', 'sort_order' => 2],
            ['name' => 'Aprobada', 'slug' => 'approved', 'description' => 'Reserva aprobada', 'sort_order' => 3],
            ['name' => 'Rechazada', 'slug' => 'rejected', 'description' => 'Reserva rechazada', 'sort_order' => 4],
            ['name' => 'Cancelada', 'slug' => 'cancelled', 'description' => 'Reserva cancelada', 'sort_order' => 5],
            ['name' => 'Completada', 'slug' => 'completed', 'description' => 'Reserva completada', 'sort_order' => 6],
        ]);

        $this->seedTable('gender_types', [
            ['name' => 'Masculino', 'slug' => 'male', 'sort_order' => 1],
            ['name' => 'Femenino', 'slug' => 'female', 'sort_order' => 2],
            ['name' => 'Otro', 'slug' => 'other', 'sort_order' => 3],
            ['name' => 'Prefiere no decir', 'slug' => 'prefer_not_to_say', 'sort_order' => 4],
        ]);

        $this->command->info('✅ Catálogos creados correctamente');
    }

    private function seedTable(string $table, array $items): void
    {
        foreach ($items as $item) {
            DB::table($table)->updateOrInsert(
                ['slug' => $item['slug']],
                array_merge($item, [
                    'is_active' => $item['is_active'] ?? true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
