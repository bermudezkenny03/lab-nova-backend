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
                'name' => 'Electronic Components',
                'description' => 'Basic electronic parts such as resistors, capacitors, diodes, transistors, and integrated circuits.',
                'status' => 1,
            ],
            [
                'name' => 'Measurement Instruments',
                'description' => 'Devices used to measure electrical or physical values, such as multimeters, oscilloscopes, and signal meters.',
                'status' => 1,
            ],
            [
                'name' => 'Development Boards',
                'description' => 'Programmable boards used for electronics, robotics, and embedded systems projects.',
                'status' => 1,
            ],
            [
                'name' => 'Power Supplies',
                'description' => 'Equipment used to provide regulated electrical power for laboratory practices and projects.',
                'status' => 1,
            ],
            [
                'name' => 'Cables & Connectors',
                'description' => 'Connection accessories such as jumper wires, USB cables, banana cables, adapters, and connectors.',
                'status' => 1,
            ],
            [
                'name' => 'Tools',
                'description' => 'Manual and technical tools such as soldering irons, pliers, screwdrivers, wire cutters, and tweezers.',
                'status' => 1,
            ],
            [
                'name' => 'Sensors & Modules',
                'description' => 'Electronic modules and sensors used in automation, robotics, IoT, and control projects.',
                'status' => 1,
            ],
            [
                'name' => 'Lab Equipment',
                'description' => 'General laboratory equipment used for academic practices, testing, and experimentation.',
                'status' => 1,
            ],
            [
                'name' => 'Computer Equipment',
                'description' => 'Computing devices and accessories such as laptops, monitors, keyboards, mice, and peripherals.',
                'status' => 1,
            ],
            [
                'name' => 'Safety Equipment',
                'description' => 'Protective equipment used to maintain safety during laboratory activities.',
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
