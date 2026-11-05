<?php

namespace Database\Factories;

use App\Enums\StoreStatus;
use App\Models\Category;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'owner_id' => User::factory()->umkmAdmin(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'description' => fake()->paragraph(),
            'logo_path' => null,
            'city' => fake()->city(),
            'address' => fake()->address(),
            'phone' => '08'.fake()->numerify('##########'),
            'status' => StoreStatus::Approved,
            'rejection_reason' => null,
            'submitted_at' => now()->subDays(2),
            'reviewed_at' => now()->subDay(),
            'reviewed_by' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StoreStatus::Pending,
            'reviewed_at' => null,
            'reviewed_by' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StoreStatus::Approved,
        ]);
    }

    public function rejected(string $reason = 'Dokumen tidak lengkap.'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StoreStatus::Rejected,
            'rejection_reason' => $reason,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StoreStatus::Suspended,
        ]);
    }
}
