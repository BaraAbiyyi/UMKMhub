<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Aturan simulasi (PRD bagian 7 & 9)
    |--------------------------------------------------------------------------
    */

    /** Ongkir simulasi tetap per pesanan (Rupiah). */
    'shipping_cost' => (int) env('UMKMHUB_SHIPPING_COST', 10000),

    /** Ambang stok menipis (jumlah unit). */
    'low_stock_threshold' => (int) env('UMKMHUB_LOW_STOCK_THRESHOLD', 5),

    /** Batas waktu bayar simulasi (menit). */
    'payment_expiry_minutes' => (int) env('UMKMHUB_PAYMENT_EXPIRY_MINUTES', 15),

    /** Batas jumlah toko per akun Admin UMKM (PRD bagian 4). */
    'max_stores_per_owner' => (int) env('UMKMHUB_MAX_STORES_PER_OWNER', 2),
];
