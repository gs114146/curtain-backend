<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Color;
use App\Models\Design;
use App\Models\Material;
use App\Models\Transparency;
use App\Models\Characteristic;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all related data
        $colors = Color::all();
        $designs = Design::all();
        $materials = Material::all();
        $transparencies = Transparency::all();
        $characteristics = Characteristic::all();

        if ($colors->isEmpty() || $designs->isEmpty() || $materials->isEmpty() || $transparencies->isEmpty() || $characteristics->isEmpty()) {
            $this->command->warn('Please run the other seeders first (ColorSeeder, DesignSeeder, MaterialSeeder, TransparencySeeder, CharacteristicSeeder)');
            return;
        }

        $products = [
            [
                'name' => 'Classic White Cotton Curtains',
                'description' => 'Beautiful white cotton curtains with a classic design, perfect for any room.',
                'sku' => 'CUR-001',
                'price' => 89.99,
                'sale_price' => 79.99,
                'stock_quantity' => 50,
                'dimensions' => '120cm x 150cm',
                'weight' => '1.2kg',
                'color_id' => $colors->where('name', 'White')->first()->id,
                'design_id' => $designs->where('name', 'Classic')->first()->id,
                'material_id' => $materials->where('name', 'Cotton')->first()->id,
                'transparency_id' => $transparencies->where('name', 'Semi-transparent')->first()->id,
                'characteristic_id' => $characteristics->where('name', 'Collection cotton & more')->first()->id,
                'status' => 'active',
                'featured' => true,
                'in_stock' => true,
                'meta_title' => 'Classic White Cotton Curtains - Premium Quality',
                'meta_description' => 'Premium white cotton curtains with classic design. Perfect for any home decor.',
            ],
            [
                'name' => 'Modern Black-Out Polyester Curtains',
                'description' => 'Modern black-out curtains made from high-quality polyester for complete light blocking.',
                'sku' => 'CUR-002',
                'price' => 129.99,
                'sale_price' => null,
                'stock_quantity' => 30,
                'dimensions' => '140cm x 180cm',
                'weight' => '1.8kg',
                'color_id' => $colors->where('name', 'Black')->first()->id,
                'design_id' => $designs->where('name', 'Plain')->first()->id,
                'material_id' => $materials->where('name', 'Polyester')->first()->id,
                'transparency_id' => $transparencies->where('name', 'Black-out')->first()->id,
                'characteristic_id' => $characteristics->where('name', 'Flame-retardant')->first()->id,
                'status' => 'active',
                'featured' => false,
                'in_stock' => true,
                'meta_title' => 'Modern Black-Out Polyester Curtains',
                'meta_description' => 'Complete light blocking curtains made from premium polyester material.',
            ],
            [
                'name' => 'Floral Pattern Linen Curtains',
                'description' => 'Elegant floral pattern curtains made from natural linen material.',
                'sku' => 'CUR-003',
                'price' => 149.99,
                'sale_price' => 129.99,
                'stock_quantity' => 25,
                'dimensions' => '130cm x 160cm',
                'weight' => '1.5kg',
                'color_id' => $colors->where('name', 'Light Pink')->first()->id,
                'design_id' => $designs->where('name', 'Floral')->first()->id,
                'material_id' => $materials->where('name', 'Linen')->first()->id,
                'transparency_id' => $transparencies->where('name', 'Semi-transparent')->first()->id,
                'characteristic_id' => $characteristics->where('name', 'Collection eco & linen')->first()->id,
                'status' => 'active',
                'featured' => true,
                'in_stock' => true,
                'meta_title' => 'Floral Pattern Linen Curtains - Natural Elegance',
                'meta_description' => 'Beautiful floral pattern curtains made from natural linen for an elegant look.',
            ],
            [
                'name' => 'Geometric Design Velvet Curtains',
                'description' => 'Luxurious velvet curtains with modern geometric design patterns.',
                'sku' => 'CUR-004',
                'price' => 199.99,
                'sale_price' => null,
                'stock_quantity' => 15,
                'dimensions' => '150cm x 200cm',
                'weight' => '2.2kg',
                'color_id' => $colors->where('name', 'Dark Grey')->first()->id,
                'design_id' => $designs->where('name', 'Geometric')->first()->id,
                'material_id' => $materials->where('name', 'Viscose')->first()->id,
                'transparency_id' => $transparencies->where('name', 'Opaque')->first()->id,
                'characteristic_id' => $characteristics->where('name', 'Velvet')->first()->id,
                'status' => 'active',
                'featured' => false,
                'in_stock' => true,
                'meta_title' => 'Geometric Design Velvet Curtains - Luxury Collection',
                'meta_description' => 'Premium velvet curtains with geometric patterns for a modern luxury look.',
            ],
            [
                'name' => 'Stripe Pattern Cotton Curtains',
                'description' => 'Classic stripe pattern curtains in neutral beige color.',
                'sku' => 'CUR-005',
                'price' => 99.99,
                'sale_price' => 89.99,
                'stock_quantity' => 40,
                'dimensions' => '120cm x 150cm',
                'weight' => '1.3kg',
                'color_id' => $colors->where('name', 'Beige')->first()->id,
                'design_id' => $designs->where('name', 'Stripe')->first()->id,
                'material_id' => $materials->where('name', 'Cotton')->first()->id,
                'transparency_id' => $transparencies->where('name', 'Semi-transparent')->first()->id,
                'characteristic_id' => $characteristics->where('name', 'Single width')->first()->id,
                'status' => 'active',
                'featured' => false,
                'in_stock' => true,
                'meta_title' => 'Stripe Pattern Cotton Curtains - Classic Style',
                'meta_description' => 'Classic stripe pattern curtains in neutral beige for timeless elegance.',
            ],
            [
                'name' => 'Embroidered Silk Curtains',
                'description' => 'Luxurious embroidered silk curtains with intricate patterns.',
                'sku' => 'CUR-006',
                'price' => 299.99,
                'sale_price' => 249.99,
                'stock_quantity' => 10,
                'dimensions' => '140cm x 180cm',
                'weight' => '1.1kg',
                'color_id' => $colors->where('name', 'Gold')->first()->id,
                'design_id' => $designs->where('name', 'Small pattern')->first()->id,
                'material_id' => $materials->where('name', 'Rayon')->first()->id,
                'transparency_id' => $transparencies->where('name', 'Transparent')->first()->id,
                'characteristic_id' => $characteristics->where('name', 'Embroidered')->first()->id,
                'status' => 'active',
                'featured' => true,
                'in_stock' => true,
                'meta_title' => 'Embroidered Silk Curtains - Luxury Collection',
                'meta_description' => 'Premium embroidered silk curtains with intricate gold patterns.',
            ],
            [
                'name' => 'Bamboo Natural Curtains',
                'description' => 'Eco-friendly bamboo curtains for a natural, sustainable look.',
                'sku' => 'CUR-007',
                'price' => 119.99,
                'sale_price' => null,
                'stock_quantity' => 20,
                'dimensions' => '130cm x 160cm',
                'weight' => '1.4kg',
                'color_id' => $colors->where('name', 'Light Green')->first()->id,
                'design_id' => $designs->where('name', 'Plain')->first()->id,
                'material_id' => $materials->where('name', 'Bamboo')->first()->id,
                'transparency_id' => $transparencies->where('name', 'Semi-transparent')->first()->id,
                'characteristic_id' => $characteristics->where('name', 'Collection eco & linen')->first()->id,
                'status' => 'active',
                'featured' => false,
                'in_stock' => true,
                'meta_title' => 'Bamboo Natural Curtains - Eco-Friendly',
                'meta_description' => 'Sustainable bamboo curtains for environmentally conscious homes.',
            ],
            [
                'name' => 'Highlights Pattern Curtains',
                'description' => 'Modern curtains with highlight patterns for contemporary interiors.',
                'sku' => 'CUR-008',
                'price' => 139.99,
                'sale_price' => 119.99,
                'stock_quantity' => 35,
                'dimensions' => '125cm x 155cm',
                'weight' => '1.6kg',
                'color_id' => $colors->where('name', 'Cornflower Blue')->first()->id,
                'design_id' => $designs->where('name', 'Highlights')->first()->id,
                'material_id' => $materials->where('name', 'Polyester')->first()->id,
                'transparency_id' => $transparencies->where('name', 'Dim-out')->first()->id,
                'characteristic_id' => $characteristics->where('name', 'Collection vibes')->first()->id,
                'status' => 'active',
                'featured' => false,
                'in_stock' => true,
                'meta_title' => 'Highlights Pattern Curtains - Modern Style',
                'meta_description' => 'Contemporary curtains with highlight patterns for modern homes.',
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        $this->command->info('Products seeded successfully!');
    }
}
