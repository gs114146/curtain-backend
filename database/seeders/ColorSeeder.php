<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Color;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = [
            // Row 1 - Neutrals (6 colors)
            ['name' => 'White', 'color_value' => '#FFFFFF', 'status' => 'active'],
            ['name' => 'Light Grey', 'color_value' => '#B8B8B8', 'status' => 'active'],
            ['name' => 'Beige', 'color_value' => '#D4C4B0', 'status' => 'active'],
            ['name' => 'Taupe', 'color_value' => '#A8A08A', 'status' => 'active'],
            ['name' => 'Medium Grey', 'color_value' => '#9B9B9B', 'status' => 'active'],
            ['name' => 'Dark Grey', 'color_value' => '#6B6B6B', 'status' => 'active'],

            // Row 2 - Warm colors (6 colors)
            ['name' => 'Yellow', 'color_value' => '#F5F563', 'status' => 'active'],
            ['name' => 'Orange', 'color_value' => '#FF8A3D', 'status' => 'active'],
            ['name' => 'Brown', 'color_value' => '#8B6914', 'status' => 'active'],
            ['name' => 'Red', 'color_value' => '#D32F2F', 'status' => 'active'],
            ['name' => 'Light Pink', 'color_value' => '#E1BEE7', 'status' => 'active'],
            ['name' => 'Gold', 'color_value' => '#D4AF37', 'status' => 'active'],

            // Row 3 - Cool colors (6 colors)
            ['name' => 'Rust Brown', 'color_value' => '#8B4513', 'status' => 'active'],
            ['name' => 'Dark Brown', 'color_value' => '#654321', 'status' => 'active'],
            ['name' => 'Light Purple', 'color_value' => '#DDA0DD', 'status' => 'active'],
            ['name' => 'Light Green', 'color_value' => '#90EE90', 'status' => 'active'],
            ['name' => 'Light Cyan', 'color_value' => '#AFEEEE', 'status' => 'active'],
            ['name' => 'Cornflower Blue', 'color_value' => '#6495ED', 'status' => 'active'],

            // Row 4 - Black (1 color)
            ['name' => 'Black', 'color_value' => '#000000', 'status' => 'active'],
        ];

        foreach ($colors as $color) {
            Color::create($color);
        }
    }
}
