<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Material;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materials = [
            ['name' => 'Acrylic', 'status' => 'active'],
            ['name' => 'Bamboo', 'status' => 'active'],
            ['name' => 'Cationic', 'status' => 'active'],
            ['name' => 'Cotton', 'status' => 'active'],
            ['name' => 'Linen', 'status' => 'active'],
            ['name' => 'Lurex', 'status' => 'active'],
            ['name' => 'Lyocell', 'status' => 'active'],
            ['name' => 'Modal', 'status' => 'active'],
            ['name' => 'Polyamide', 'status' => 'active'],
            ['name' => 'Polyester', 'status' => 'active'],
            ['name' => 'Polyester fr', 'status' => 'active'],
            ['name' => 'Rayon', 'status' => 'active'],
            ['name' => 'Recycled cotton', 'status' => 'active'],
            ['name' => 'Recycled polyester', 'status' => 'active'],
            ['name' => 'Tencel', 'status' => 'active'],
            ['name' => 'Trevira cs', 'status' => 'active'],
            ['name' => 'Viscose', 'status' => 'active'],
            ['name' => 'Wool', 'status' => 'active'],
        ];

        foreach ($materials as $material) {
            Material::create($material);
        }
    }
}
