<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop old FK + column
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'total_amount', 'status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('code', 20)->unique()->after('id');
            $table->foreignId('user_id')->after('code')->constrained()->restrictOnDelete();
            $table->foreignId('store_id')->after('user_id')->constrained()->restrictOnDelete();
            $table->enum('status', [
                'pending_payment',
                'paid',
                'processing',
                'shipped',
                'completed',
                'cancelled',
            ])->default('pending_payment')->after('store_id');
            $table->unsignedBigInteger('subtotal')->after('status');
            $table->unsignedBigInteger('shipping_cost')->default(10000)->after('subtotal');
            $table->unsignedBigInteger('total')->after('shipping_cost');
            $table->json('shipping_address')->after('total');
            $table->timestamp('expires_at')->nullable()->after('shipping_address');

            $table->index('user_id');
            $table->index('store_id');
            $table->index('status');
        });

        // Update order_items: rename price → unit_price, add product_name, drop subtotal
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('product_name', 150)->after('product_id');
            $table->renameColumn('price', 'unit_price');
            $table->dropColumn('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('product_name');
            $table->renameColumn('unit_price', 'price');
            $table->decimal('subtotal', 12, 2)->after('price');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['store_id']);
            $table->dropColumn([
                'code', 'user_id', 'store_id', 'status',
                'subtotal', 'shipping_cost', 'total',
                'shipping_address', 'expires_at',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->constrained();
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
        });
    }
};
