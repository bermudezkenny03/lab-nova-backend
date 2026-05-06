<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_requests', function (Blueprint $table) {
            $table->softDeletes()->after('status');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->softDeletes()->after('generated_at');
        });
    }

    public function down(): void
    {
        Schema::table('report_requests', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};