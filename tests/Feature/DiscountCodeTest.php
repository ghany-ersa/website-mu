<?php

namespace Tests\Feature;

use App\Models\DiscountCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountCodeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Regression test: the create form's _form.blade.php partial used to access
     * $discountCode->code (etc.) directly rather than $discountCode?->code, which threw
     * "Attempt to read property on null" because create.blade.php explicitly sets
     * $discountCode = null (a defined null, not an undefined variable) before including it.
     */
    public function test_admin_can_view_create_form(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.discount-codes.create'))
            ->assertOk()
            ->assertSee('Kode Diskon Baru');
    }

    public function test_admin_can_create_discount_code(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.discount-codes.store'), [
            'code' => 'HEMAT20',
            'type' => 'percent',
            'value' => 20,
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('discount_codes', ['code' => 'HEMAT20', 'value' => 20]);
    }

    public function test_admin_can_view_edit_form_for_existing_code(): void
    {
        $admin = User::factory()->admin()->create();
        $discountCode = DiscountCode::create([
            'code' => 'LAMA10',
            'type' => 'percent',
            'value' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.discount-codes.edit', $discountCode))
            ->assertOk()
            ->assertSee('LAMA10');
    }

    public function test_non_admin_cannot_view_create_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.discount-codes.create'))
            ->assertForbidden();
    }
}
