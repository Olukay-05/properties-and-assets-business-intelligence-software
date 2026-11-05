<?php

namespace Database\Factories;

use App\Domain\Tenancy\Enums\MembershipRole;
use App\Domain\Tenancy\Enums\MembershipStatus;
use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Organisation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    /**
     * @var class-string<Membership>
     */
    protected $model = Membership::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'roles' => [MembershipRole::Technician->value],
            'client_scope' => null,
            'status' => MembershipStatus::Active,
        ];
    }

    /**
     * Bind the membership to a specific organisation.
     */
    public function forOrganisation(Organisation $organisation): static
    {
        return $this->state(fn (array $attributes) => [
            'organisation_id' => $organisation->getKey(),
        ]);
    }

    /**
     * @param  list<MembershipRole|string>  $roles
     */
    public function withRoles(array $roles): static
    {
        return $this->state(fn (array $attributes) => [
            'roles' => array_map(
                fn (MembershipRole|string $role): string => $role instanceof MembershipRole ? $role->value : $role,
                $roles,
            ),
        ]);
    }

    /**
     * @param  list<string>|null  $clientAccountIds
     */
    public function withClientScope(?array $clientAccountIds): static
    {
        return $this->state(fn (array $attributes) => [
            'client_scope' => $clientAccountIds,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MembershipStatus::Suspended,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MembershipStatus::Inactive,
        ]);
    }
}
