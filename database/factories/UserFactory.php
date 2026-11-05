<?php

namespace Database\Factories;

use App\Domain\Tenancy\Models\Membership;
use App\Domain\Tenancy\Models\Organisation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            /* @chisel-2fa */
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            /* @end-chisel-2fa */
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Give the created user an active membership in a new organisation.
     *
     * A user without an active membership cannot open a tenant context, so any
     * authenticated request is refused with 403 (Story 1.2). Tests that act as
     * a user therefore need a real membership, exactly like a real user does.
     */
    public function withTenant(): static
    {
        return $this->afterCreating(function (User $user): void {
            $organisation = Organisation::factory()->create();

            // Fixtures write membership rows with the tenant bound at the
            // database level only: FORCE RLS applies to the owner too, so an
            // unbound insert would be rejected.
            DB::transaction(function () use ($organisation, $user): void {
                DB::select("select set_config('app.org_id', ?, true)", [$organisation->id]);

                Membership::factory()
                    ->forOrganisation($organisation)
                    ->for($user)
                    ->create();
            });
        });
    }

    /* @chisel-2fa */
    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
    /* @end-chisel-2fa */
}
