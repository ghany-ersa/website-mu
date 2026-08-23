<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationType;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrganizationBrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_brand_colors_are_copied_from_template_on_creation(): void
    {
        $user = User::factory()->create();
        $type = OrganizationType::factory()->create();
        Template::factory()->create([
            'organization_type_id' => $type->id,
            'is_active' => true,
            'structure' => ['brand' => ['primary' => '#123456', 'secondary' => '#abcdef']],
        ]);

        $this->actingAs($user)->post(route('organizations.store'), [
            'organization_type_id' => $type->id,
            'name' => 'Brand Test Org',
            'slug' => 'brand-test-org-'.uniqid(),
        ])->assertRedirect();

        $organization = Organization::where('name', 'Brand Test Org')->firstOrFail();
        $this->assertSame('#123456', $organization->primary_color);
        $this->assertSame('#abcdef', $organization->secondary_color);
    }

    public function test_organization_without_template_has_no_brand_color_and_falls_back_to_platform_default(): void
    {
        $organization = Organization::factory()->create(['template_id' => null]);

        $this->assertNull($organization->primary_color);
        $this->assertSame('#2C368B', $organization->primaryColor());
        $this->assertSame('#079C4E', $organization->secondaryColor());
    }

    public function test_member_can_update_brand_settings(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.brand.update', $organization), [
                'primary_color' => '#ff0000',
                'secondary_color' => '#00ff00',
                'logo' => 'https://example.test/logo.png',
                'phone' => '0331 123456',
                'email' => 'kontak@example.test',
                'whatsapp' => '6281234567890',
                'address' => 'Jl. Contoh No. 1, Ambulu, Jember',
                'instagram_url' => 'https://instagram.com/example',
                'facebook_url' => 'https://facebook.com/example',
            ])
            ->assertRedirect();

        $organization->refresh();
        $this->assertSame('#ff0000', $organization->primary_color);
        $this->assertSame('#00ff00', $organization->secondary_color);
        $this->assertSame('https://example.test/logo.png', $organization->logo);
        $this->assertSame('0331 123456', $organization->phone);
        $this->assertSame('kontak@example.test', $organization->email);
        $this->assertSame('6281234567890', $organization->whatsapp);
        $this->assertSame('Jl. Contoh No. 1, Ambulu, Jember', $organization->address);
        $this->assertSame('https://instagram.com/example', $organization->instagram_url);
        $this->assertSame('https://facebook.com/example', $organization->facebook_url);
    }

    public function test_invalid_email_is_rejected(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.brand.update', $organization), [
                'email' => 'not-an-email',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_invalid_social_urls_are_rejected(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.brand.update', $organization), [
                'instagram_url' => 'not-a-url',
                'facebook_url' => 'also-not-a-url',
            ])
            ->assertSessionHasErrors(['instagram_url', 'facebook_url']);
    }

    public function test_non_member_cannot_view_or_update_brand_settings(): void
    {
        $stranger = User::factory()->create();
        $organization = Organization::factory()->create();

        $this->actingAs($stranger)->get(route('organizations.brand.edit', $organization))->assertForbidden();
        $this->actingAs($stranger)->patch(route('organizations.brand.update', $organization), [])->assertForbidden();
    }

    public function test_invalid_color_format_is_rejected(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.brand.update', $organization), [
                'primary_color' => 'not-a-color',
            ])
            ->assertSessionHasErrors('primary_color');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function tooLightColorProvider(): array
    {
        return [
            'pure white' => ['#FFFFFF'],
            'near-white gray' => ['#F5F5F5'],
            'near-white' => ['#FEFEFE'],
            'pure yellow' => ['#FFFF00'],
        ];
    }

    #[DataProvider('tooLightColorProvider')]
    public function test_too_light_secondary_color_is_rejected(string $hex): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.brand.update', $organization), [
                'secondary_color' => $hex,
            ])
            ->assertSessionHasErrors('secondary_color');

        $this->assertNull($organization->fresh()->secondary_color);
    }

    public function test_too_light_primary_color_is_rejected(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.brand.update', $organization), [
                'primary_color' => '#FFFFFF',
            ])
            ->assertSessionHasErrors('primary_color');
    }

    public function test_readable_secondary_color_is_accepted(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.brand.update', $organization), [
                'secondary_color' => '#FFC107',
            ])
            ->assertRedirect()
            ->assertSessionDoesntHaveErrors();

        $this->assertSame('#FFC107', $organization->fresh()->secondary_color);
    }

    public function test_too_light_color_is_rejected_on_organization_creation(): void
    {
        $user = User::factory()->create();
        $type = OrganizationType::factory()->create();

        $this->actingAs($user)->post(route('organizations.store'), [
            'organization_type_id' => $type->id,
            'name' => 'White Brand Org',
            'slug' => 'white-brand-org-'.uniqid(),
            'secondary_color' => '#FFFFFF',
        ])->assertSessionHasErrors('secondary_color');

        $this->assertDatabaseMissing('organizations', ['name' => 'White Brand Org']);
    }

    public function test_onboarding_checklist_reflects_logo_and_contact(): void
    {
        $organization = Organization::factory()->create(['logo' => null]);
        $this->assertSame(
            ['brand' => false, 'contact' => false, 'content' => false, 'published' => false],
            $organization->onboardingChecklist()
        );

        $organization->update(['logo' => 'https://example.test/logo.png', 'whatsapp' => '6281234567890']);

        $checklist = $organization->fresh()->onboardingChecklist();
        $this->assertTrue($checklist['brand']);
        $this->assertTrue($checklist['contact']);
        $this->assertFalse($checklist['content']);
        $this->assertFalse($checklist['published']);
    }

    public function test_onboarding_checklist_content_is_done_once_a_page_has_a_section(): void
    {
        $organization = Organization::factory()->create();
        $this->assertFalse($organization->onboardingChecklist()['content']);

        $page = $organization->pages()->create(['name' => 'Beranda', 'slug' => 'beranda', 'order' => 0]);
        $page->sections()->create(['key' => 'hero', 'content' => [], 'order' => 0]);

        $this->assertTrue($organization->fresh()->onboardingChecklist()['content']);
    }

    public function test_font_family_and_border_radius_can_be_updated(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.brand.update', $organization), [
                'font_family' => 'Inter',
                'border_radius' => 'sharp',
            ])
            ->assertRedirect()
            ->assertSessionDoesntHaveErrors();

        $organization->refresh();
        $this->assertSame('Inter', $organization->font_family);
        $this->assertSame('sharp', $organization->border_radius);
        $this->assertSame('Inter', $organization->fontFamily());
        $this->assertSame('sharp', $organization->borderRadius());
    }

    public function test_invalid_font_family_is_rejected(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $organization->members()->attach($user->id, ['role' => OrganizationRole::Owner->value]);

        $this->actingAs($user)
            ->patch(route('organizations.brand.update', $organization), [
                'font_family' => 'Comic Sans',
            ])
            ->assertSessionHasErrors('font_family');
    }
}
