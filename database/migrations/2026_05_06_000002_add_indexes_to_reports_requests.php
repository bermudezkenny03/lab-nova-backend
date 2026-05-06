<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->index(['file_type']);
        });

        Schema::table('report_requests', function (Blueprint $table) {
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['file_type']);
        });

        Schema::table('report_requests', function (Blueprint $table) {
            $table->dropIndex(['type', 'created_at']);
        });
    }
};