<?php

namespace Tests\Feature;

use App\Enums\DiscountCodeType;
use App\Enums\PlanChangeRequestStatus;
use App\Models\DiscountCode;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use App\Rules\ImageFitsInMemory;
use App\Services\GoogleMapsEmbedResolver;
use App\Services\SafeUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Regression tests for the 2026-10-28 security audit (~/audits/website-mu-security-audit-2026-10-28.md).
 * Each test names the finding it pins down, so a failure here points straight at which
 * hole reopened.
 */
class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function ownerWithSection(string $key): array
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $page = OrganizationPage::factory()->create(['organization_id' => $organization->id, 'slug' => 'home']);
        $section = $page->sections()->create(['key' => $key, 'content' => [], 'order' => 1]);

        return [$owner, $organization, $section];
    }

    // ---------------------------------------------------------------- #1 map_embed XSS

    public function test_a_javascript_map_embed_is_rejected_instead_of_stored(): void
    {
        [$owner, $organization, $section] = $this->ownerWithSection('lokasi-peta');

        foreach (['javascript:alert(document.domain)//maps/embed', 'javascript:alert(1)'] as $payload) {
            $this->actingAs($owner)
                ->patchJson(route('organizations.sections.update', [$organization, $section]), [
                    'content' => ['map_embed' => $payload],
                ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('content.map_embed');

            $this->assertNull($section->fresh()->content['map_embed'] ?? null);
        }
    }

    public function test_a_real_embed_url_is_still_accepted(): void
    {
        [$owner, $organization, $section] = $this->ownerWithSection('lokasi-peta');
        $embed = 'https://www.google.com/maps/embed?pb=!1m18!1m12';

        $this->actingAs($owner)
            ->patchJson(route('organizations.sections.update', [$organization, $section]), [
                'content' => ['map_embed' => $embed],
            ])
            ->assertOk();

        $this->assertSame($embed, $section->fresh()->content['map_embed']);
    }

    /**
     * Rows saved before the save-time check existed must not reach the iframe either.
     */
    public function test_a_legacy_javascript_map_embed_is_not_rendered(): void
    {
        $html = view('templates.sections.lokasi-peta.standar', [
            'section' => ['content' => ['map_embed' => 'javascript:alert(1)']],
        ])->render();

        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('<iframe', $html);
    }

    // ---------------------------------------------------------------- #2 CTA URL XSS

    public function test_a_javascript_cta_url_is_rejected(): void
    {
        [$owner, $organization, $section] = $this->ownerWithSection('hero');

        $this->actingAs($owner)
            ->patchJson(route('organizations.sections.update', [$organization, $section]), [
                'content' => ['cta_type' => 'url', 'cta_url' => 'javascript:alert(1)'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('content.cta_url');
    }

    public function test_ordinary_cta_urls_are_still_accepted(): void
    {
        [$owner, $organization, $section] = $this->ownerWithSection('hero');

        foreach (['https://example.com/daftar', '/donasi', '#kontak', 'mailto:info@example.com'] as $url) {
            $this->actingAs($owner)
                ->patchJson(route('organizations.sections.update', [$organization, $section]), [
                    'content' => ['cta_type' => 'url', 'cta_url' => $url],
                ])
                ->assertOk();

            $this->assertSame($url, $section->fresh()->content['cta_url']);
        }
    }

    public function test_a_legacy_javascript_cta_url_is_not_rendered(): void
    {
        $html = view('templates.sections.cta.standar', [
            'section' => ['content' => ['cta_type' => 'url', 'cta_label' => 'Klik', 'cta_url' => 'javascript:alert(1)']],
            'organization' => null,
        ])->render();

        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_safe_url_refuses_dangerous_schemes_and_obfuscation(): void
    {
        foreach (['javascript:alert(1)', 'JaVaScRiPt:alert(1)', "java\tscript:alert(1)", ' javascript:alert(1)', 'data:text/html,x', 'vbscript:x', '//evil.example'] as $url) {
            $this->assertNull(SafeUrl::href($url), $url);
        }
    }

    // ---------------------------------------------------------------- #3 SSRF

    public function test_the_map_resolver_never_fetches_a_non_google_host(): void
    {
        Http::fake();

        foreach ([
            'http://169.254.169.254/latest/meta-data/?goo.gl',
            'http://10.0.0.5:6379/#google.com/maps',
            'https://evil.example/google.com/maps/place/x',
            'https://www.google.com.evil.example/maps',
        ] as $url) {
            $this->assertNull(GoogleMapsEmbedResolver::resolve($url), $url);
        }

        Http::assertNothingSent();
    }

    public function test_the_map_resolver_does_not_follow_a_redirect_off_google(): void
    {
        Http::fake([
            'maps.app.goo.gl/*' => Http::response('', 302, ['Location' => 'http://169.254.169.254/@1.0,2.0']),
            '*' => Http::response('should not be requested', 200),
        ]);

        $this->assertNull(GoogleMapsEmbedResolver::resolve('https://maps.app.goo.gl/abc123'));

        Http::assertSentCount(1);
    }

    // ---------------------------------------------------------------- #4 rate limiting

    public function test_login_locks_out_after_repeated_wrong_passwords(): void
    {
        $user = User::factory()->create(['password' => bcrypt('benar-sekali')]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'salah']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'benar-sekali'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    // ---------------------------------------------------------------- #5 member enumeration

    public function test_adding_a_member_does_not_reveal_their_name(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();
        $target = User::factory()->create(['name' => 'Nama Rahasia Korban']);

        $this->actingAs($owner)
            ->post(route('organizations.members.store', $organization), ['email' => $target->email])
            ->assertSessionHas('status', fn (string $status) => ! str_contains($status, 'Nama Rahasia Korban'));
    }

    // ---------------------------------------------------------------- #6 discount codes

    private function checkout(User $owner, Organization $organization, Plan $plan, string $code)
    {
        return $this->actingAs($owner)->post(route('organizations.plan.store', $organization), [
            'plan_id' => $plan->id,
            'duration_months' => 3,
            'discount_code' => $code,
        ]);
    }

    public function test_a_capped_discount_code_cannot_be_redeemed_past_its_cap(): void
    {
        $code = DiscountCode::create(['code' => 'SATUKALI', 'type' => DiscountCodeType::Percent, 'value' => 10, 'max_uses' => 1, 'is_active' => true]);

        $this->assertTrue($code->claimUse());
        $this->assertFalse($code->claimUse(), 'A second claim succeeded on a code capped at one use.');
        $this->assertSame(1, $code->fresh()->used_count);
    }

    public function test_an_organization_cannot_reuse_a_code_but_gets_it_back_on_cancel(): void
    {
        config(['billing.manual_transfer.only' => true]);
        $plan = Plan::create(['key' => 'uji', 'name' => 'Uji', 'price_monthly' => 18_000, 'is_active' => true]);
        $code = DiscountCode::create(['code' => 'HEMAT', 'type' => DiscountCodeType::Percent, 'value' => 10, 'is_active' => true]);
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();

        $this->checkout($owner, $organization, $plan, 'HEMAT')->assertSessionHasNoErrors();
        $this->assertSame(1, $code->fresh()->used_count);

        $pending = $organization->planChangeRequests()->first();
        $pending->update(['status' => PlanChangeRequestStatus::Approved]);

        $this->checkout($owner, $organization, $plan, 'HEMAT')->assertSessionHasErrors('discount_code');

        // A cancelled request never got the discount, so it neither blocks a retry nor keeps
        // the use.
        $pending->update(['status' => PlanChangeRequestStatus::Pending]);
        $this->actingAs($owner)
            ->delete(route('organizations.plan.cancel', [$organization, $pending]))
            ->assertRedirect();

        $this->assertSame(0, $code->fresh()->used_count);
        $this->checkout($owner, $organization, $plan, 'HEMAT')->assertSessionHasNoErrors();
    }

    // ---------------------------------------------------------------- #7 content shape

    public function test_oversized_or_deeply_nested_section_content_is_rejected(): void
    {
        [$owner, $organization, $section] = $this->ownerWithSection('hero');

        $this->actingAs($owner)
            ->patchJson(route('organizations.sections.update', [$organization, $section]), [
                'content' => ['headline' => str_repeat('a', 20001)],
            ])
            ->assertUnprocessable();

        $this->actingAs($owner)
            ->patchJson(route('organizations.sections.update', [$organization, $section]), [
                'content' => ['headline' => ['a' => ['b' => ['c' => 'x']]]],
            ])
            ->assertUnprocessable();
    }

    // ---------------------------------------------------------------- #8 headers

    public function test_security_headers_are_set(): void
    {
        $this->get(route('home'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Content-Security-Policy');
    }

    // ---------------------------------------------------------------- #10 image bombs

    /**
     * A PNG that is only a header declaring the given size - getimagesize() reads no further,
     * so this stands in for a highly compressed "decompression bomb" without ever allocating it.
     */
    private function pngHeader(int $width, int $height): UploadedFile
    {
        $ihdr = 'IHDR'.pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0);
        $bytes = "\x89PNG\r\n\x1a\n".pack('N', 13).$ihdr.pack('N', crc32($ihdr));

        $path = tempnam(sys_get_temp_dir(), 'bomb');
        file_put_contents($path, $bytes);

        return new UploadedFile($path, 'bom.png', 'image/png', null, true);
    }

    public function test_a_decompression_bomb_upload_is_refused_with_a_readable_message(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();

        $this->actingAs($owner)
            ->postJson(route('organizations.media.store', $organization), ['files' => [$this->pngHeader(30000, 30000)]])
            ->assertUnprocessable()
            ->assertJsonPath('errors', fn (array $errors) => str_contains(implode(' ', array_merge(...array_values($errors))), 'terlalu besar'));
    }

    /**
     * HEIC passes Laravel's `image` rule but getimagesize() can't read it. The size check must
     * not be the thing that rejects it (that produced "invalid image dimensions") - the
     * decoder's own "simpan ulang sebagai JPG" message is the one that explains what to do.
     */
    public function test_an_image_whose_size_cannot_be_read_is_left_to_the_decoder(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'heic');
        file_put_contents($path, "\x00\x00\x00\x18ftypheic".str_repeat("\x00", 64));
        $file = new UploadedFile($path, 'foto.heic', 'image/heic', null, true);

        $this->assertTrue(Validator::make(['file' => $file], ['file' => [new ImageFitsInMemory]])->passes());
    }

    public function test_an_ordinary_large_photo_still_fits(): void
    {
        // The rule budgets against memory_limit minus what's already in use, and a long test
        // run holds far more than a real upload request does (~22MB) - so give it a limit that
        // reflects a request's headroom instead of whatever the runner has accumulated.
        $previous = ini_set('memory_limit', '512M');

        try {
            $this->assertTrue(Validator::make(
                ['file' => $this->pngHeader(4000, 3000)],
                ['file' => [new ImageFitsInMemory]],
            )->passes());
        } finally {
            ini_set('memory_limit', (string) $previous);
        }
    }

    // ---------------------------------------------------------------- #9 template switch

    public function test_switching_to_an_inactive_or_private_template_is_refused(): void
    {
        $owner = User::factory()->create();
        $organization = Organization::factory()->withOwner($owner)->create();

        foreach ([['is_active' => false], ['is_public' => false]] as $attributes) {
            $template = Template::factory()->create($attributes);

            $this->actingAs($owner)
                ->patch(route('organizations.template.update', $organization), ['template_id' => $template->id])
                ->assertSessionHasErrors('template_id');
        }
    }
}
