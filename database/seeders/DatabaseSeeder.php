<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User for Authentication
        User::firstOrCreate(
            ['email' => 'admin@umkmhub.id'],
            [
                'name' => 'Admin UMKMHub',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Categories
        $snack = Category::create([
            'name' => 'Makanan Ringan',
            'description' => 'Aneka keripik dan camilan tradisional UMKM khas Malang',
        ]);

        $beverage = Category::create([
            'name' => 'Minuman',
            'description' => 'Kopi lokal dan aneka minuman olahan petani setempat',
        ]);

        // 3. Products
        $product1 = Product::create([
            'category_id' => $snack->id,
            'name' => 'Keripik Tempe Renyah',
            'description' => 'Keripik tempe gurih renyah kemasan 200 gram khas Malang',
            'price' => 15000,
            'is_active' => true,
        ]);

        $product2 = Product::create([
            'category_id' => $beverage->id,
            'name' => 'Kopi Robusta Dampit 250g',
            'description' => 'Kopi robusta petik merah lereng Semeru kemasan 250 gram',
            'price' => 35000,
            'is_active' => true,
        ]);

        // 4. Stocks (Optimistic Concurrency Control with version)
        Stock::create([
            'product_id' => $product1->id,
            'quantity' => 50,
            'version' => 1,
        ]);

        Stock::create([
            'product_id' => $product2->id,
            'quantity' => 30,
            'version' => 1,
        ]);

        // 5. Customer
        Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jl. Semanggi Timur No. 12, Malang',
        ]);
    }
}
