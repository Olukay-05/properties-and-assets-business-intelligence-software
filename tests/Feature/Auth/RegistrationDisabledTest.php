<?php

namespace Tests\Feature\Auth;

use Laravel\Fortify\Features;
use Tests\TestCase;

/**
 * Public registration is disabled for Phase 1 (AUTH.md §4.1).
 *
 * Every user must belong to an FM organisation through an explicit membership,
 * and there is no public tenant sign-up: accounts are provisioned
 * administratively. This guards against the starter kit's `registration`
 * feature being re-enabled by accident, which would let an anonymous visitor
 * create an orphan `users` row with no membership.
 */
class RegistrationDisabledTest extends TestCase
{
    public function test_the_registration_feature_is_not_enabled(): void
    {
        expect(Features::enabled(Features::registration()))->toBeFalse();
    }

    public function test_the_registration_screen_does_not_exist(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_the_registration_endpoint_does_not_exist(): void
    {
        $this->post('/register', [
            'name' => 'Anonymous Visitor',
            'email' => 'anonymous@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'anonymous@example.test']);
    }
}
