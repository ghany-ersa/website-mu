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
        foreach (['starter' => 0, 'organization' => 0, 'professional' => 60] as $planKey => $expected) {
            $plan = Plan::where('key', $planKey)->first();
            $this->assertNotNull($plan, "Plan {$planKey} missing");
            $this->assertSame($expected, $plan->limits->firstWhere('key', 'students')?->max_count);
            $this->assertNotNull($plan->limits->firstWhere('key', 'achievements'));
        }

        $this->assertIsArray($organization->planViolations());
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
