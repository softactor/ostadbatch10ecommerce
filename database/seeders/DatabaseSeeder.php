<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run()
    {
        // Create user
        User::create([
            'name' => 'Test User',
            'mobile' => '01712345678',
            'email' => 'test@example.com',
            'address' => 'Dhaka, Bangladesh',
            'password' => 'password'
        ]);
        
        // Create brands
        $brands = ['Apple', 'Samsung', 'Xiaomi', 'OnePlus', 'Realme'];
        foreach ($brands as $brand) {
            Brand::create(['name' => $brand, 'slug' => strtolower($brand), 'is_active' => true]);
        }
        
        // Create categories
        $electronics = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        Category::create(['name' => 'Mobile Phones', 'slug' => 'mobile-phones', 'parent_id' => $electronics->id]);
        Category::create(['name' => 'Laptops', 'slug' => 'laptops', 'parent_id' => $electronics->id]);
        
        // Create products
        for ($i = 1; $i <= 20; $i++) {
            Product::create([
                'name' => "Product $i",
                'slug' => "product-$i",
                'description' => "Description for product $i",
                'price' => rand(1000, 50000),
                'discount_price' => rand(800, 40000),
                'stock' => rand(0, 100),
                'brand_id' => rand(1, 5),
                'category_id' => rand(2, 3),
                'is_featured' => $i <= 5,
                'is_active' => true
            ]);
        }
        
        // Create sliders
        Slider::create(['title' => 'Summer Sale', 'subtitle' => 'Up to 50% off', 'image' => 'slider1.jpg', 'order' => 1, 'is_active' => true]);
        Slider::create(['title' => 'New Arrivals', 'subtitle' => 'Latest products', 'image' => 'slider2.jpg', 'order' => 2, 'is_active' => true]);
    }

}
