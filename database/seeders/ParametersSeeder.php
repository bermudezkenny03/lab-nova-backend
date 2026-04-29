<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParametersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('parameters')->delete(); // Evita problemas de claves foráneas

        $now = now();

        // Géneros
        DB::table('parameters')->insert([
            ['name' => 'Male', 'parameter_type_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Female', 'parameter_type_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Others', 'parameter_type_id' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
