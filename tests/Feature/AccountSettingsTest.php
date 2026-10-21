<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_account_page_requires_login(): void
    {
        $this->get(route('account.edit'))->assertRedirect(route('login'));
    }

    public function test_a_user_can_set_their_whatsapp_number(): void
    {
        $user = User::factory()->create(['whatsapp' => null]);

        $this->actingAs($user)
            ->patch(route('account.whatsapp.update'), ['whatsapp' => '0812 3456 7890'])
            ->assertSessionHasNoErrors();

        $this->assertSame('6281234567890', $user->fresh()->whatsapp);
    }

    public function test_an_invalid_whatsapp_number_is_rejected(): void
    {
        $user = User::factory()->create(['whatsapp' => '6281234567890']);

        $this->actingAs($user)
            ->patch(route('account.whatsapp.update'), ['whatsapp' => '12345'])
            ->assertSessionHasErrors('whatsapp');

        $this->assertSame('6281234567890', $user->fresh()->whatsapp);
    }

    public function test_the_reminder_banner_shows_only_while_the_number_is_missing(): void
    {
        $without = User::factory()->create(['whatsapp' => null]);
        $with = User::factory()->create(['whatsapp' => '6281234567890']);

        $this->actingAs($without)->get(route('organizations.index'))->assertSee('Lengkapi nomor WhatsApp Anda');
        $this->actingAs($with)->get(route('organizations.index'))->assertDontSee('Lengkapi nomor WhatsApp Anda');
    }

    public function test_a_user_can_change_their_password_with_the_current_one(): void
    {
        $user = User::factory()->create(['password' => Hash::make('LamaSekali123!')]);

        $this->actingAs($user)
            ->patch(route('account.password.update'), [
                'current_password' => 'LamaSekali123!',
                'password' => 'BaruSekali123!',
                'password_confirmation' => 'BaruSekali123!',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('BaruSekali123!', $user->fresh()->password));
    }

    public function test_a_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => Hash::make('LamaSekali123!')]);

        $this->actingAs($user)
            ->patch(route('account.password.update'), [
                'current_password' => 'SalahTebak123!',
                'password' => 'BaruSekali123!',
                'password_confirmation' => 'BaruSekali123!',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('LamaSekali123!', $user->fresh()->password));
    }
}
