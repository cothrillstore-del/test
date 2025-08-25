<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Create Brands
        $brands = [
            ['name' => 'TaylorMade', 'country' => 'USA', 'is_featured' => true],
            ['name' => 'Callaway', 'country' => 'USA', 'is_featured' => true],
            ['name' => 'Titleist', 'country' => 'USA', 'is_featured' => true],
            ['name' => 'Ping', 'country' => 'USA', 'is_featured' => false],
            ['name' => 'Mizuno', 'country' => 'Japan', 'is_featured' => false],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }

        // Create Categories
        $categories = [
            ['name' => 'Drivers', 'sort_order' => 1],
            ['name' => 'Irons', 'sort_order' => 2],
            ['name' => 'Putters', 'sort_order' => 3],
            ['name' => 'Wedges', 'sort_order' => 4],
            ['name' => 'Golf Balls', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create sample products
        $product = Product::create([
            'brand_id' => 1,
            'category_id' => 1,
            'name' => 'Stealth 2 Plus Driver',
            'model_year' => '2024',
            'description' => 'The Stealth 2 Plus Driver features advanced carbon technology...',
            'short_description' => 'Premium driver with carbon face technology',
            'price_min' => 599.99,
            'price_max' => 649.99,
            'retail_price' => 699.99,
            'status' => 'active',
            'is_featured' => true,
            'specifications' => [
                'Loft Options' => '8°, 9°, 10.5°',
                'Shaft Options' => 'Multiple premium shafts',
                'Head Size' => '460cc',
            ],
            'features' => [
                '60X Carbon Twist Face',
                'Adjustable weight system',
                'Speed pocket technology',
            ],
        ]);

        // Create variants
        ProductVariant::create([
            'product_id' => $product->id,
            'variant_type' => 'loft',
            'variant_name' => '9 Degree',
            'variant_value' => '9°',
            'price' => 599.99,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'variant_type' => 'flex',
            'variant_name' => 'Stiff Flex',
            'variant_value' => 'S',
            'price' => 599.99,
        ]);
    }
}