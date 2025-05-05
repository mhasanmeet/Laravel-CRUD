<?php

namespace Database\Factories;

use App\Constants\ApprovalStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 100, 1000),
            'status' => ApprovalStatus::DRAFT,
            'author_id' => UserFactory::new()->create()->id,
            // 'author_id' => UserRole::factory(),
        ];
    }
}
