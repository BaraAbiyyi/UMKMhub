<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Customer;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $cat = Category::create([
            'name' => 'Makanan Ringan',
            'description' => 'Aneka keripik dan jajanan pasar'
        ]);

        $prod = Product::create([
            'category_id' => $cat->id,
            'name' => 'Keripik Tempe Renyah',
            'description' => 'Keripik tempe gurih khas Malang 200g',
            'price' => 15000,
            'is_active' => true,
        ]);

        Stock::create([
            'product_id' => $prod->id,
            'quantity' => 50,
            'version' => 1,
        ]);

        Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 10, Tegal'
        ]);
    }
}