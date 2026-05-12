<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('report_request_type_id')->constrained('report_request_types')->restrictOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('report_request_status_id')->constrained('report_request_statuses')->restrictOnDelete();
            $table->json('filters')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_requests');
    }
};
