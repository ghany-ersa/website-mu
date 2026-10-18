<?php

namespace Tests\Feature;

use App\Enums\OrganizationStatus;
use App\Models\Achievement;
use App\Models\GalleryPhoto;
use App\Models\Officer;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Program;
use App\Models\Student;
use App\Models\Template;
use App\Models\User;
use Database\Seeders\OrganizationTypeSeeder;
use Database\Seeders\SdMadyaTemplateSeeder;
use Database\Seeders\SectionVariantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the entities the SD-Madya school template needs: the columns added to Officer /
 * Program / GalleryPhoto, the new Student and Achievement models with their many-to-many link,
 * and the public pupil profile page.
 */
class SchoolCmsTest extends TestCase
{
    use RefreshDatabase;

    private function tenantUrl(Organization $organization, string $path): string
    {
        return 'http://'.$organization->slug.'.'.config('tenancy.domain').$path;
    }

    public function test_officer_carries_teacher_fields_and_active_scope(): void
    {
        $organization = Organization::factory()->create();

        $teacher = $organization->officers()->create([
            'slug' => 'siti-aminah',
            'name' => 'Siti Aminah',
            'role' => 'Wali Kelas 6A',
            'subject' => 'Matematika',
            'bio' => 'Membangun logika berpikir sejak dini.',
            'is_active' => true,
        ]);
        $organization->officers()->create([
            'slug' => 'guru-nonaktif',
            'name' => 'Guru Nonaktif',
            'role' => 'Guru',
            'is_active' => false,
        ]);

        $this->assertSame('Matematika', $teacher->fresh()->subject);
        $this->assertSame([$teacher->id], $organization->officers()->active()->pluck('id')->all());
    }

    public function test_program_supports_kegiatan_type_with_image_and_category(): void
    {
        $organization = Organization::factory()->create();

        $organization->programs()->create([
            'type' => 'kegiatan',
            'title' => 'Sholat Dhuha',
            'category' => 'Keagamaan',
            'image' => 'dhuha.jpg',
        ]);
        $organization->programs()->create(['type' => 'program', 'title' => 'Program Biasa']);

        $kegiatan = $organization->programs()->ofType('kegiatan')->get();

        $this->assertCount(1, $kegiatan);
        $this->assertSame('Keagamaan', $kegiatan->first()->category);
        // The existing types must be unaffected by the new one.
        $this->assertCount(1, $organization->programs()->ofType('program')->get());
    }

    public function test_gallery_photo_carries_category_and_hides_when_inactive(): void
    {
        $organization = Organization::factory()->create();

        $organization->photos()->create(['url' => 'a.jpg', 'title' => 'Wisuda', 'category' => 'Kegiatan', 'is_active' => true]);
        $organization->photos()->create(['url' => 'b.jpg', 'title' => 'Disembunyikan', 'is_active' => false]);

        $active = $organization->photos()->active()->get();

        $this->assertCount(1, $active);
        $this->assertSame('Kegiatan', $active->first()->category);
    }

    public function test_achievement_can_credit_several_students(): void
    {
        $organization = Organization::factory()->create();

        $students = collect(['Ahmad', 'Siti', 'Rizky'])->map(fn (string $name, int $index) => $organization->students()->create([
            'slug' => 'siswa-'.$index,
            'name' => $name,
        ]));

        $achievement = $organization->achievements()->create([
            'title' => 'Juara 2 Cerdas Cermat',
            'category' => 'Akademik',
            'year' => '2026',
        ]);
        $achievement->students()->sync($students->pluck('id'));

        // A team event is the reason this is a pivot rather than a student_id column.
        $this->assertCount(3, $achievement->fresh()->students);
        $this->assertCount(1, $students->first()->fresh()->achievements);
    }

    public function test_deleting_a_student_keeps_the_achievement(): void
    {
        $organization = Organization::factory()->create();
        $student = $organization->students()->create(['slug' => 'ahmad', 'name' => 'Ahmad']);
        $achievement = $organization->achievements()->create(['title' => 'Juara 1']);
        $achievement->students()->sync([$student->id]);

        $student->delete();

        $this->assertTrue($achievement->fresh()->exists);
        $this->assertCount(0, $achievement->fresh()->students);
    }

    public function test_achievements_are_listed_newest_first(): void
    {
        $organization = Organization::factory()->create();
        $organization->achievements()->create(['title' => 'Lama', 'year' => '2023']);
        $organization->achievements()->create(['title' => 'Baru', 'year' => '2026']);

        $this->assertSame(['Baru', 'Lama'], $organization->achievements()->pluck('title')->all());
    }

    public function test_student_hobbies_round_trip_as_a_list(): void
    {
        $organization = Organization::factory()->create();
        $student = $organization->students()->create([
            'slug' => 'ahmad',
            'name' => 'Ahmad Fauzi',
            'hobbies' => ['Membaca', 'Catur'],
        ]);

        $this->assertSame(['Membaca', 'Catur'], $student->fresh()->hobbies);
        $this->assertSame('AF', $student->initials());
    }

    public function test_student_profile_page_renders_with_achievements(): void
    {
        $organization = Organization::factory()->create(['status' => OrganizationStatus::Published]);
        $student = $organization->students()->create([
            'slug' => 'ahmad-fauzi',
            'name' => 'Ahmad Fauzi',
            'grade' => 'Kelas 6',
            'quote' => 'Belajar adalah investasi terbaik.',
            'hobbies' => ['Catur'],
        ]);
        $achievement = $organization->achievements()->create([
            'title' => 'Juara 1 Olimpiade Matematika',
            'category' => 'Akademik',
            'level' => 'Tingkat Kabupaten',
            'year' => '2026',
        ]);
        $achievement->students()->sync([$student->id]);

        $response = $this->get($this->tenantUrl($organization, '/siswa/ahmad-fauzi'));

        $response->assertOk();
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('Juara 1 Olimpiade Matematika');
        $response->assertSee('Tingkat Kabupaten');
        $response->assertSee('Catur');
        $response->assertSee('<title>Ahmad Fauzi - '.$organization->name.'</title>', false);
    }

    public function test_inactive_student_profile_page_is_not_found(): void
    {
        $organization = Organization::factory()->create(['status' => OrganizationStatus::Published]);
        $organization->students()->create([
            'slug' => 'disembunyikan',
            'name' => 'Siswa Disembunyikan',
            'is_active' => false,
        ]);

        // Hiding a pupil in the CMS must take their public page down with them, not just drop
        // them from the listing.
        $this->get($this->tenantUrl($organization, '/siswa/disembunyikan'))->assertNotFound();
    }

    public function test_student_slugs_are_unique_per_organization_only(): void
    {
        $first = Organization::factory()->create();
        $second = Organization::factory()->create();

        $first->students()->create(['slug' => 'budi-santoso', 'name' => 'Budi Santoso']);
        $second->students()->create(['slug' => 'budi-santoso', 'name' => 'Budi Santoso']);

        $this->assertSame(1, Student::where('slug', 'budi-santoso')->where('organization_id', $first->id)->count());
        $this->assertSame(1, Student::where('slug', 'budi-santoso')->where('organization_id', $second->id)->count());
    }

    public function test_deleting_an_organization_removes_its_school_records(): void
    {
        $organization = Organization::factory()->create();
        $student = $organization->students()->create(['slug' => 'ahmad', 'name' => 'Ahmad']);
        $achievement = $organization->achievements()->create(['title' => 'Juara 1']);
        $achievement->students()->sync([$student->id]);
        $officer = $organization->officers()->create(['slug' => 'ketua', 'name' => 'Ketua', 'role' => 'Ketua']);
        $program = $organization->programs()->create(['type' => 'kegiatan', 'title' => 'Futsal']);
        $photo = $organization->photos()->create(['url' => 'a.jpg']);

        $organization->delete();

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('achievements', ['id' => $achievement->id]);
        $this->assertDatabaseMissing('achievement_student', ['achievement_id' => $achievement->id]);
        $this->assertDatabaseMissing('officers', ['id' => $officer->id]);
        $this->assertDatabaseMissing('programs', ['id' => $program->id]);
        $this->assertDatabaseMissing('gallery_photos', ['id' => $photo->id]);
    }

    public function test_plan_limits_gate_students_and_achievements(): void
    {
        $organization = Organization::factory()->create();

        // Every plan carries both keys after the seeding migration, so a school on a lower tier
        // is blocked from pupil profiles rather than hitting a missing-limit fallback.
        // Keys are the seeded plan keys (PlanSeeder), not the user-facing names: 'professional'
        // was renamed to 'eksklusif' and this assertion was left behind pointing at a plan that
        // no longer exists. 'premium' is included because it is the fallback plan an
        // organization with no plan_id resolves to (PlanLimitService::memoizedFallbackPlan()).
        // Only these three exist - pupil profiles stay Eksklusif-only, so the two lower tiers
        // must both cap students at 0.
        foreach (['starter' => 0, 'premium' => 0, 'eksklusif' => 60] as $planKey => $expected) {
            $plan = Plan::where('key', $planKey)->first();
            $this->assertNotNull($plan, "Plan {$planKey} missing");
            $this->assertSame($expected, $plan->limits->firstWhere('key', 'students')?->max_count);
            $this->assertNotNull($plan->limits->firstWhere('key', 'achievements'));
        }

        $this->assertNull(Plan::where('key', 'organization')->first(), 'the retired "organization" tier must not be seeded');

        $this->assertIsArray($organization->planViolations());
    }

    /**
     * Regression: the sections_total cap drops sections backward from the LAST page, so on a
     * tight plan it used to strip every non-home page of the SD Madya template down to nothing
     * while still creating those pages - the owner got a nav full of pages holding only a header
     * and a footer. Whole pages must be skipped instead, and the home page must always survive.
     */
    public function test_pages_emptied_by_the_sections_cap_are_skipped_entirely(): void
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(SectionVariantSeeder::class);
        $this->seed(SdMadyaTemplateSeeder::class);

        $template = Template::where('slug', SdMadyaTemplateSeeder::SLUG)->firstOrFail();
        $starter = Plan::where('key', 'starter')->firstOrFail();

        $organization = Organization::create([
            'organization_type_id' => $template->organization_type_id,
            'template_id' => $template->id,
            'plan_id' => $starter->id,
            'name' => 'SD Uji Coba',
            'slug' => 'sd-uji-coba',
            'status' => OrganizationStatus::Draft,
        ]);

        $organization->ensureHomePageExists();
        $organization->load('pages.sections');

        $lockedKeys = collect(config('page-builder.sections'))
            ->filter(fn (array $section) => $section['locked'] ?? false)
            ->keys();

        $this->assertTrue($organization->pages->firstWhere('is_home', true) !== null, 'home page must survive');

        foreach ($organization->pages as $page) {
            $unlocked = $page->sections->reject(fn ($section) => $lockedKeys->contains($section->key));

            $this->assertGreaterThan(
                0,
                $unlocked->count(),
                "Page [{$page->slug}] was created with only locked sections - it should have been skipped."
            );
        }
    }

    /**
     * The `stats` strip on prestasi is owner-editable from the builder. It previously fell
     * through to the "Kelola daftar ini dari CMS (segera hadir)" placeholder, because the editor
     * branch was hardcoded to tentang-organisasi - so the four stats the SD Madya template seeds
     * could be rendered but never changed. The cap comes from each section's own `stats_max`
     * (4 here, 3 for tentang-organisasi), matching its grid width.
     */
    public function test_prestasi_stats_editor_renders_with_its_own_cap(): void
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(SectionVariantSeeder::class);
        $this->seed(SdMadyaTemplateSeeder::class);

        $template = Template::where('slug', SdMadyaTemplateSeeder::SLUG)->firstOrFail();
        $user = User::factory()->create();

        $organization = Organization::create([
            'user_id' => $user->id,
            'organization_type_id' => $template->organization_type_id,
            'template_id' => $template->id,
            'plan_id' => Plan::where('key', 'eksklusif')->first()->id,
            'name' => 'SD Stats',
            'slug' => 'sd-stats',
        ]);
        $organization->members()->attach($user->id, ['role' => 'owner']);
        $organization->ensureHomePageExists();

        $page = $organization->fresh()->pages()->where('slug', 'prestasi')->firstOrFail();

        $response = $this->actingAs($user)
            ->get(route('organizations.builder.page', [$organization, $page]));

        $response->assertOk();
        // The editor, not the "segera hadir" placeholder.
        $response->assertSee('Tambah statistik (maks 4)');
        $response->assertSee('content[stats]', false);
        // The four stats the template seeds - verifiable school facts, not invented totals.
        $response->assertSee('Rombongan Belajar', false);
        $response->assertSee('Berdiri Sejak', false);

        // Saving: edited rows persist...
        $section = $page->sections()->where('key', 'prestasi')->firstOrFail();

        $this->actingAs($user)->patch(
            route('organizations.sections.update', [$organization, $section]),
            ['content' => ['stats' => [
                ['value' => '600+', 'label' => 'Trofi Baru'],
                ['value' => '99%', 'label' => 'Kelulusan'],
            ]]]
        )->assertRedirect();

        $this->assertSame(
            [['value' => '600+', 'label' => 'Trofi Baru'], ['value' => '99%', 'label' => 'Kelulusan']],
            $section->fresh()->content['stats']
        );

        // ...and clearing every row actually empties the strip rather than silently
        // keeping the previous value (the hidden input carries the empty case).
        $this->actingAs($user)->patch(
            route('organizations.sections.update', [$organization, $section]),
            ['content' => ['stats' => '']]
        )->assertRedirect();

        $this->assertEmpty(array_filter((array) $section->fresh()->content['stats']));
    }

    /**
     * `prestasi` and `kegiatan` were the only CMS-backed sections with no branch in
     * CmsSampleDataSeeder, so they seeded nothing. Both auto-bind to their CMS table whenever
     * $organization is in scope - always, on a real page and in the builder canvas - so their
     * $content['items'] fallbacks never applied and the two most school-specific sections
     * rendered "Belum ada prestasi." in the very editor meant to show the design filled in.
     */
    public function test_school_sections_seed_sample_content_for_a_sandbox(): void
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(SectionVariantSeeder::class);
        $this->seed(SdMadyaTemplateSeeder::class);

        $template = Template::where('slug', SdMadyaTemplateSeeder::SLUG)->firstOrFail();

        $sandbox = Organization::create([
            'organization_type_id' => $template->organization_type_id,
            'template_id' => $template->id,
            'is_sandbox' => true,
            'name' => 'Sandbox SD',
            'slug' => 'sandbox-sd-cms',
        ]);

        $sandbox->ensureHomePageExists();

        $this->assertGreaterThan(0, $sandbox->achievements()->count(), 'prestasi must not render its empty state');
        $this->assertGreaterThan(0, $sandbox->programs()->where('type', 'kegiatan')->count(), 'kegiatan must not render its empty state');

        // The filter rail on prestasi/standar is built from the categories present, so the
        // samples have to span more than one to produce a meaningful filter.
        $this->assertGreaterThan(1, $sandbox->achievements()->distinct()->pluck('category')->filter()->count());
    }

    /**
     * The staff roster is the school's real org chart, so it has to survive the trip into
     * Officer rows intact - including `subject`, which seedOfficers() previously dropped, and
     * which is what the sekolahmu variant renders as the teaching-assignment chip.
     */
    public function test_school_staff_roster_seeds_with_roles_and_subjects(): void
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(SectionVariantSeeder::class);
        $this->seed(SdMadyaTemplateSeeder::class);

        $template = Template::where('slug', SdMadyaTemplateSeeder::SLUG)->firstOrFail();

        $sandbox = Organization::create([
            'organization_type_id' => $template->organization_type_id,
            'template_id' => $template->id,
            'is_sandbox' => true,
            'name' => 'Sandbox SD Staf',
            'slug' => 'sandbox-sd-staf',
        ]);
        $sandbox->ensureHomePageExists();

        $head = $sandbox->officers()->orderBy('order')->first();

        // Ordered by seniority, not by the chart's layout, so truncation keeps the head first.
        $this->assertSame('Beni Hendarto, S.Pd.I', $head->name);
        $this->assertSame('Kepala Sekolah', $head->role);
        $this->assertSame('Pendidikan Agama Islam', $head->subject);

        // Teaching assignments must persist, not just titles.
        $this->assertGreaterThan(0, $sandbox->officers()->whereNotNull('subject')->count());

        // Real named people never get stand-in stock portraits.
        $this->assertSame(0, $sandbox->officers()->whereNotNull('photo')->count());
    }

    /** The school's own contact details reach the template's sandbox. */
    public function test_school_contact_details_are_carried_by_the_template(): void
    {
        $this->seed(OrganizationTypeSeeder::class);
        $this->seed(SectionVariantSeeder::class);
        $this->seed(SdMadyaTemplateSeeder::class);

        $contact = Template::where('slug', SdMadyaTemplateSeeder::SLUG)->firstOrFail()->structure['contact'];

        // Bare digits with country code - the shape wa.me and formulir-kontak both expect.
        $this->assertSame('6282332583556', $contact['whatsapp']);
        $this->assertStringStartsWith('https://', $contact['instagram_url']);
        $this->assertStringStartsWith('https://', $contact['youtube_url']);
    }

    public function test_school_models_are_registered_for_the_organization(): void
    {
        $organization = Organization::factory()->create();

        $this->assertInstanceOf(Student::class, $organization->students()->make());
        $this->assertInstanceOf(Achievement::class, $organization->achievements()->make());
        $this->assertInstanceOf(Officer::class, $organization->officers()->make());
        $this->assertInstanceOf(Program::class, $organization->programs()->make());
        $this->assertInstanceOf(GalleryPhoto::class, $organization->photos()->make());
    }
}
