<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Prevent exact duplicate reservations (same equipment and exact same start/end)
            $table->unique(['equipment_id', 'start_time', 'end_time'], 'reservations_equipment_start_end_unique');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropUnique('reservations_equipment_start_end_unique');
        });
    }
};