<?php

namespace Database\Factories;

use App\Domain\Tenancy\Enums\OrganisationStatus;
use App\Domain\Tenancy\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organisation>
 */
class OrganisationFactory extends Factory
{
    /**
     * @var class-string<Organisation>
     */
    protected $model = Organisation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'status' => OrganisationStatus::Active,
        ];
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrganisationStatus::Suspended,
        ]);
    }
}
