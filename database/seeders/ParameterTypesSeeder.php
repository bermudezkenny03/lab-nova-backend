<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParameterTypesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('parameter_types')->delete();

        DB::table('parameter_types')->insert([
            ['id' => 1, 'name' => 'Gender', 'table_reference' => 'users', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}