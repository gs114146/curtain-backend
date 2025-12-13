<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usage;

class UsageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usageData = [
            ['name' => 'Shree', 'status' => 'active'],
            ['name' => 'Throws', 'status' => 'active'],
            ['name' => 'Decoration Fabric', 'status' => 'active'],
            ['name' => 'Upholstery Fabric', 'status' => 'active'],
        ];

        foreach ($usageData as $usage) {
            Usage::create($usage);
        }
    }
}
