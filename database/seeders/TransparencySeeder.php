<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transparency;

class TransparencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transparencies = [
            ['name' => 'Transparent', 'status' => 'active'],
            ['name' => 'Semi-transparent', 'status' => 'active'],
            ['name' => 'Opaque', 'status' => 'active'],
            ['name' => 'Dim-out', 'status' => 'active'],
            ['name' => 'Black-out', 'status' => 'active'],
        ];

        foreach ($transparencies as $transparency) {
            Transparency::create($transparency);
        }
    }
}
