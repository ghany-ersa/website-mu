<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The front door - the only route that creates a User. Covers what the registration form must
 * refuse as much as what it accepts, since a duplicate email or an unhashed password here
 * affects every other access check in the app.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_registration_form_renders(): void
    {
        $this->get(route('register'))->assertOk();
    }

    public function test_a_new_user_can_register_and_is_logged_in(): void
    {
        Event::fake([Registered::class]);

        $this->post(route('register'), [
            'name' => 'Pengurus Ranting',
            'email' => 'pengurus@example.test',
            'password' => 'RahasiaKuat123!',
            'password_confirmation' => 'RahasiaKuat123!',
        ])->assertRedirect();

        $user = User::where('email', 'pengurus@example.test')->first();

        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);
        Event::assertDispatched(Registered::class);
    }

    public function test_the_password_is_stored_hashed(): void
    {
        $this->post(route('register'), [
            'name' => 'Pengurus Ranting',
            'email' => 'hash@example.test',
            'password' => 'RahasiaKuat123!',
            'password_confirmation' => 'RahasiaKuat123!',
        ]);

        $user = User::where('email', 'hash@example.test')->first();

        $this->assertNotSame('RahasiaKuat123!', $user->password);
        $this->assertTrue(Hash::check('RahasiaKuat123!', $user->password));
    }

    public function test_a_registered_user_is_not_an_admin_by_default(): void
    {
        $this->post(route('register'), [
            'name' => 'Pengurus Ranting',
            'email' => 'biasa@example.test',
            'password' => 'RahasiaKuat123!',
            'password_confirmation' => 'RahasiaKuat123!',
        ]);

        $this->assertFalse((bool) User::where('email', 'biasa@example.test')->first()->is_admin);
    }

    public function test_a_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'sudah@example.test']);

        $this->post(route('register'), [
            'name' => 'Orang Lain',
            'email' => 'sudah@example.test',
            'password' => 'RahasiaKuat123!',
            'password_confirmation' => 'RahasiaKuat123!',
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, User::where('email', 'sudah@example.test')->count());
        $this->assertGuest();
    }

    public function test_a_mismatched_password_confirmation_is_rejected(): void
    {
        $this->post(route('register'), [
            'name' => 'Pengurus Ranting',
            'email' => 'beda@example.test',
            'password' => 'RahasiaKuat123!',
            'password_confirmation' => 'SalahKetik123!',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'beda@example.test']);
        $this->assertGuest();
    }

    public function test_an_invalid_email_is_rejected(): void
    {
        $this->post(route('register'), [
            'name' => 'Pengurus Ranting',
            'email' => 'bukan-email',
            'password' => 'RahasiaKuat123!',
            'password_confirmation' => 'RahasiaKuat123!',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_missing_required_fields_are_rejected(): void
    {
        $this->post(route('register'), [])
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertSame(0, User::count());
    }

    public function test_a_logged_in_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect();

        $this->assertGuest();
    }
}
