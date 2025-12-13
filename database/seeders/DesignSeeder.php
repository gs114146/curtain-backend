<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Design;

class DesignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designs = [
            ['name' => 'Plain', 'status' => 'active'],
            ['name' => 'Small pattern', 'status' => 'active'],
            ['name' => 'Geometric', 'status' => 'active'],
            ['name' => 'Stripe', 'status' => 'active'],
            ['name' => 'Classic', 'status' => 'active'],
            ['name' => 'Floral', 'status' => 'active'],
            ['name' => 'Highlights', 'status' => 'active'],
        ];

        foreach ($designs as $design) {
            Design::create($design);
        }
    }
}
