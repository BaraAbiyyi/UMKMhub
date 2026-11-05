<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(10000, 500000);
        $shippingCost = 10000;

        return [
            'code' => 'ORD-'.strtoupper(Str::random(8)),
            'user_id' => User::factory()->customer(),
            'store_id' => Store::factory()->approved(),
            'status' => OrderStatus::PendingPayment,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total' => $subtotal + $shippingCost,
            'shipping_address' => [
                'recipient' => fake()->name(),
                'phone' => '08'.fake()->numerify('##########'),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
            ],
            'expires_at' => now()->addMinutes(15),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Paid,
            'expires_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Completed,
            'expires_at' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Cancelled,
            'expires_at' => null,
        ]);
    }
}
