<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────
        // 1. SUPERADMIN
        // ─────────────────────────────────────────────
        $superadmin = User::create([
            'name' => 'Super Admin UMKMHub',
            'email' => 'superadmin@umkmhub.id',
            'password' => Hash::make('password123'),
            'role' => UserRole::Superadmin,
        ]);

        // ─────────────────────────────────────────────
        // 2. KATEGORI
        // ─────────────────────────────────────────────
        $categories = [
            ['name' => 'Makanan Ringan', 'description' => 'Aneka keripik dan camilan tradisional UMKM'],
            ['name' => 'Minuman', 'description' => 'Kopi lokal dan aneka minuman olahan'],
            ['name' => 'Fashion', 'description' => 'Pakaian dan aksesoris buatan pengrajin lokal'],
            ['name' => 'Kerajinan Tangan', 'description' => 'Produk seni dan kerajinan UMKM lokal'],
            ['name' => 'Pertanian & Perkebunan', 'description' => 'Produk segar dan olahan petani lokal'],
            ['name' => 'Kuliner', 'description' => 'Makanan siap saji dan olahan khas daerah'],
        ];

        $createdCategories = [];
        foreach ($categories as $cat) {
            $createdCategories[] = Category::create($cat);
        }

        [$snack, $beverage, $fashion, $craft, $agri, $food] = $createdCategories;

        // ─────────────────────────────────────────────
        // 3. ADMIN UMKM & TOKO DEMO
        // ─────────────────────────────────────────────

        // Toko 1: Keripik Bu Sari
        $sari = User::create([
            'name' => 'Sari Rahayu',
            'email' => 'sari@umkmhub.id',
            'password' => Hash::make('password123'),
            'role' => UserRole::UmkmAdmin,
        ]);

        $tokoSari = Store::create([
            'owner_id' => $sari->id,
            'category_id' => $snack->id,
            'name' => 'Keripik Bu Sari',
            'slug' => 'keripik-bu-sari',
            'description' => 'Keripik tempe dan singkong asli Malang, dibuat tanpa pengawet sejak 2010.',
            'city' => 'Malang',
            'address' => 'Jl. Semanggi Timur No. 12, Malang',
            'phone' => '081234567890',
            'status' => StoreStatus::Approved,
            'submitted_at' => now()->subDays(5),
            'reviewed_at' => now()->subDays(4),
            'reviewed_by' => $superadmin->id,
        ]);

        // Toko 2: Kopi Pak Budi (satu akun 2 toko)
        $budi = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@umkmhub.id',
            'password' => Hash::make('password123'),
            'role' => UserRole::UmkmAdmin,
        ]);

        $tokoBudi = Store::create([
            'owner_id' => $budi->id,
            'category_id' => $beverage->id,
            'name' => 'Kopi Dampit Budi',
            'slug' => 'kopi-dampit-budi',
            'description' => 'Kopi robusta petik merah dari lereng Semeru, diproses natural dan honey.',
            'city' => 'Lumajang',
            'address' => 'Jl. Raya Dampit No. 45, Lumajang',
            'phone' => '085678901234',
            'status' => StoreStatus::Approved,
            'submitted_at' => now()->subDays(10),
            'reviewed_at' => now()->subDays(9),
            'reviewed_by' => $superadmin->id,
        ]);

        // Toko 3: Toko pending (belum disetujui)
        $rina = User::create([
            'name' => 'Rina Handayani',
            'email' => 'rina@umkmhub.id',
            'password' => Hash::make('password123'),
            'role' => UserRole::UmkmAdmin,
        ]);

        Store::create([
            'owner_id' => $rina->id,
            'category_id' => $fashion->id,
            'name' => 'Batik Rina Craft',
            'slug' => 'batik-rina-craft',
            'description' => 'Batik tulis dan cap khas Yogyakarta, motif tradisional dan modern.',
            'city' => 'Yogyakarta',
            'address' => 'Jl. Prawirotaman No. 8, Yogyakarta',
            'phone' => '089012345678',
            'status' => StoreStatus::Pending,
            'submitted_at' => now()->subHours(3),
        ]);

        // ─────────────────────────────────────────────
        // 4. PRODUK & STOK — Toko Sari
        // ─────────────────────────────────────────────
        $products = [
            [
                'store_id' => $tokoSari->id,
                'category_id' => $snack->id,
                'name' => 'Keripik Tempe Renyah',
                'description' => 'Keripik tempe gurih renyah kemasan 200g, dibuat dari tempe segar tanpa MSG.',
                'price' => 15000,
                'is_active' => true,
                'qty' => 50,
            ],
            [
                'store_id' => $tokoSari->id,
                'category_id' => $snack->id,
                'name' => 'Keripik Singkong Pedas',
                'description' => 'Singkong pilihan, digoreng tipis dengan bumbu pedas khas Malang. Kemasan 150g.',
                'price' => 12000,
                'is_active' => true,
                'qty' => 3, // stok menipis
            ],
            [
                'store_id' => $tokoSari->id,
                'category_id' => $snack->id,
                'name' => 'Keripik Bayam Crispy',
                'description' => 'Bayam segar digoreng crispy, camilan sehat bebas pengawet. Kemasan 100g.',
                'price' => 10000,
                'is_active' => false, // nonaktif
                'qty' => 20,
            ],
        ];

        // Produk & Stok — Toko Budi
        $products = array_merge($products, [
            [
                'store_id' => $tokoBudi->id,
                'category_id' => $beverage->id,
                'name' => 'Kopi Robusta Dampit 250g',
                'description' => 'Kopi robusta petik merah lereng Semeru, proses natural, aroma coklat.',
                'price' => 35000,
                'is_active' => true,
                'qty' => 30,
            ],
            [
                'store_id' => $tokoBudi->id,
                'category_id' => $beverage->id,
                'name' => 'Kopi Arabika Flores 200g',
                'description' => 'Single origin Flores, proses washed, cita rasa floral dan bright acidity.',
                'price' => 55000,
                'is_active' => true,
                'qty' => 15,
            ],
            [
                'store_id' => $tokoBudi->id,
                'category_id' => $beverage->id,
                'name' => 'Drip Coffee Bag Robusta (10pcs)',
                'description' => 'Kopi celup praktis, cocok untuk perjalanan. Isi 10 sachet per kemasan.',
                'price' => 45000,
                'is_active' => true,
                'qty' => 2, // stok menipis
            ],
        ]);

        $createdProducts = [];
        foreach ($products as $productData) {
            $qty = $productData['qty'];
            unset($productData['qty']);

            $product = Product::create($productData);
            Stock::create([
                'product_id' => $product->id,
                'quantity' => $qty,
                'version' => 1,
            ]);
            $createdProducts[] = $product;
        }

        // ─────────────────────────────────────────────
        // 5. PEMBELI DEMO
        // ─────────────────────────────────────────────
        $dimas = User::create([
            'name' => 'Dimas Pratama',
            'email' => 'dimas@gmail.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::Customer,
        ]);

        Address::create([
            'user_id' => $dimas->id,
            'recipient' => 'Dimas Pratama',
            'phone' => '081298765432',
            'address' => 'Jl. Sudirman No. 55, Kec. Lowokwaru',
            'city' => 'Malang',
            'is_default' => true,
        ]);

        // Keranjang demo
        $cart = Cart::create(['user_id' => $dimas->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $createdProducts[0]->id, // Keripik Tempe
            'quantity' => 2,
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $createdProducts[3]->id, // Kopi Robusta
            'quantity' => 1,
        ]);

        // ─────────────────────────────────────────────
        // 6. PESANAN DEMO
        // ─────────────────────────────────────────────
        $shippingSnapshot = [
            'recipient' => 'Dimas Pratama',
            'phone' => '081298765432',
            'address' => 'Jl. Sudirman No. 55, Kec. Lowokwaru',
            'city' => 'Malang',
        ];

        // Pesanan selesai (completed)
        $order1 = Order::create([
            'code' => 'ORD-DEMO0001',
            'user_id' => $dimas->id,
            'store_id' => $tokoSari->id,
            'status' => OrderStatus::Completed,
            'subtotal' => 30000,
            'shipping_cost' => 10000,
            'total' => 40000,
            'shipping_address' => $shippingSnapshot,
            'expires_at' => null,
        ]);
        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $createdProducts[0]->id,
            'product_name' => 'Keripik Tempe Renyah',
            'unit_price' => 15000,
            'quantity' => 2,
        ]);
        Payment::create([
            'order_id' => $order1->id,
            'method' => 'simulation',
            'status' => PaymentStatus::Success,
            'paid_at' => now()->subDays(2),
        ]);

        // Pesanan baru (paid, belum diproses)
        $order2 = Order::create([
            'code' => 'ORD-DEMO0002',
            'user_id' => $dimas->id,
            'store_id' => $tokoBudi->id,
            'status' => OrderStatus::Paid,
            'subtotal' => 35000,
            'shipping_cost' => 10000,
            'total' => 45000,
            'shipping_address' => $shippingSnapshot,
            'expires_at' => null,
        ]);
        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $createdProducts[3]->id,
            'product_name' => 'Kopi Robusta Dampit 250g',
            'unit_price' => 35000,
            'quantity' => 1,
        ]);
        Payment::create([
            'order_id' => $order2->id,
            'method' => 'simulation',
            'status' => PaymentStatus::Success,
            'paid_at' => now()->subHours(2),
        ]);

        // Pesanan menunggu pembayaran
        Order::create([
            'code' => 'ORD-DEMO0003',
            'user_id' => $dimas->id,
            'store_id' => $tokoSari->id,
            'status' => OrderStatus::PendingPayment,
            'subtotal' => 12000,
            'shipping_cost' => 10000,
            'total' => 22000,
            'shipping_address' => $shippingSnapshot,
            'expires_at' => now()->addMinutes(10),
        ]);
    }
}
