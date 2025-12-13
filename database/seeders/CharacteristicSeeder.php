<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Characteristic;

class CharacteristicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $characteristics = [
            ['name' => 'Latest collection', 'status' => 'active'],
            ['name' => 'Collection eco & linen', 'status' => 'active'],
            ['name' => 'Collection delicate vol.1 & 2', 'status' => 'active'],
            ['name' => 'Double width', 'status' => 'active'],
            ['name' => 'Single width', 'status' => 'active'],
            ['name' => 'Panel design', 'status' => 'active'],
            ['name' => 'Jacquard', 'status' => 'active'],
            ['name' => 'Burn-out', 'status' => 'active'],
            ['name' => 'Embroidered', 'status' => 'active'],
            ['name' => 'Print', 'status' => 'active'],
            ['name' => 'Crush', 'status' => 'active'],
            ['name' => 'Velvet', 'status' => 'active'],
            ['name' => 'Net', 'status' => 'active'],
            ['name' => 'Double sheer/fabric', 'status' => 'active'],
            ['name' => 'Flame-retardant', 'status' => 'active'],
            ['name' => 'Teflon', 'status' => 'active'],
            ['name' => '20.000+ abrasion cycles', 'status' => 'active'],
            ['name' => 'Collection elf', 'status' => 'active'],
            ['name' => 'Collection naturalia', 'status' => 'active'],
            ['name' => 'Collection cotton & more', 'status' => 'active'],
            ['name' => 'Collection stories', 'status' => 'active'],
            ['name' => 'Collection vibes', 'status' => 'active'],
            ['name' => 'Collection directions', 'status' => 'active'],
            ['name' => 'Collection earthlight', 'status' => 'active'],
            ['name' => 'Collection delicate 2021', 'status' => 'active'],
            ['name' => 'Collection numbers', 'status' => 'active'],
            ['name' => 'Collection tessera', 'status' => 'active'],
            ['name' => 'Collection kalesma', 'status' => 'active'],
            ['name' => 'Collection hundred', 'status' => 'active'],
        ];

        foreach ($characteristics as $characteristic) {
            Characteristic::create($characteristic);
        }
    }
}
