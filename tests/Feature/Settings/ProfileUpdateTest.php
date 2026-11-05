<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    public function test_profile_page_is_displayed()
    {
        $user = User::factory()->withTenant()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('profile.edit'));

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated()
    {
        $user = User::factory()->withTenant()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $this->assertSame('Test User', $user->refresh()->name);
    }

    public function test_the_email_address_cannot_be_changed_through_the_profile()
    {
        // A changed address redirects the password-reset flow, so it is an
        // account-takeover path. The database withholds UPDATE on
        // `users.email`; validation refuses it first so the user sees a clear
        // message instead of a server error.
        $user = User::factory()->withTenant()->create();
        $original = $user->email;

        $response = $this
            ->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => 'attacker@example.test',
            ]);

        $response
            ->assertSessionHasErrors('email')
            ->assertRedirect(route('profile.edit'));

        $this->assertSame($original, $user->refresh()->email);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged()
    {
        $user = User::factory()->withTenant()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_users_cannot_delete_their_own_account()
    {
        // User records are never hard-deleted (AUTH.md §7) and the runtime role
        // holds no DELETE privilege on `users` (RLS-POLICIES.md §4.1). Access is
        // revoked by suspending the membership instead.
        $user = User::factory()->withTenant()->create();

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertForbidden();

        $this->assertNotNull($user->fresh());
    }

    public function test_account_deletion_is_refused_regardless_of_password()
    {
        // The refusal is a policy decision, not a credential check: closing an
        // account is an administrative action, so even the correct password
        // cannot delete the record.
        $user = User::factory()->withTenant()->create();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), ['password' => 'wrong-password'])
            ->assertForbidden();

        $this->assertNotNull($user->fresh());
    }
}
