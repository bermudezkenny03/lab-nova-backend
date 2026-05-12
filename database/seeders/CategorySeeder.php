<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Componentes electrónicos',
                'description' => 'Partes electrónicas básicas como resistencias, capacitores, diodos, transistores y circuitos integrados.',
                'status' => 1,
            ],
            [
                'name' => 'Instrumentos de medición',
                'description' => 'Dispositivos utilizados para medir valores eléctricos o físicos, como multímetros, osciloscopios y medidores de señal.',
                'status' => 1,
            ],
            [
                'name' => 'Placas de desarrollo',
                'description' => 'Placas programables utilizadas en proyectos de electrónica, robótica y sistemas embebidos.',
                'status' => 1,
            ],
            [
                'name' => 'Fuentes de alimentación',
                'description' => 'Equipos utilizados para proporcionar energía eléctrica regulada en prácticas y proyectos de laboratorio.',
                'status' => 1,
            ],
            [
                'name' => 'Cables y conectores',
                'description' => 'Accesorios de conexión como cables jumper, cables USB, cables banana, adaptadores y conectores.',
                'status' => 1,
            ],
            [
                'name' => 'Herramientas',
                'description' => 'Herramientas manuales y técnicas como cautines, pinzas, destornilladores, cortadores de cable y tweezers.',
                'status' => 1,
            ],
            [
                'name' => 'Sensores y módulos',
                'description' => 'Módulos electrónicos y sensores utilizados en proyectos de automatización, robótica, IoT y control.',
                'status' => 1,
            ],
            [
                'name' => 'Equipos de laboratorio',
                'description' => 'Equipos generales de laboratorio utilizados para prácticas académicas, pruebas y experimentación.',
                'status' => 1,
            ],
            [
                'name' => 'Equipos de cómputo',
                'description' => 'Dispositivos y accesorios informáticos como laptops, monitores, teclados, mouse y periféricos.',
                'status' => 1,
            ],
            [
                'name' => 'Equipos de seguridad',
                'description' => 'Equipos de protección utilizados para mantener la seguridad durante las actividades de laboratorio.',
                'status' => 1,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'status' => $category['status'],
                ]
            );
        }
    }
}
