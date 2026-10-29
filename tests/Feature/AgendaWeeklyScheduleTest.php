<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Models\Agenda;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Kajian rutin: an agenda that repeats every week is entered as a weekday + time rather than a
 * date, and shows "Setiap <hari>" instead of a date that would go stale after one week. Also
 * covers the speaker (narasumber) field that replaced the contact person on agendas.
 */
class AgendaWeeklyScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    private OrganizationPage $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        $this->organization->members()->attach($this->user->id, ['role' => OrganizationRole::Owner->value]);
        $this->page = OrganizationPage::factory()->create(['organization_id' => $this->organization->id, 'slug' => 'home']);
        $this->page->sections()->create(['key' => 'agenda', 'content' => [], 'order' => 0]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_a_weekly_agenda_is_saved_from_a_day_and_time(): void
    {
        // A Wednesday, so Thursday (4) is "the next one", not today.
        Carbon::setTestNow('2026-10-28 10:00');

        $this->actingAs($this->user)
            ->post(route('organizations.agendas.store', $this->organization), [
                'title' => 'Kajian Tafsir',
                'schedule' => 'weekly',
                'day_of_week' => 4,
                'time_label' => "Ba'da Maghrib",
                'speaker' => 'Ustadz Fulan, Lc.',
                'status' => 'published',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $agenda = $this->organization->agendas()->sole();

        $this->assertTrue($agenda->repeats_weekly);
        $this->assertSame(Carbon::THURSDAY, $agenda->starts_at->dayOfWeek);
        $this->assertSame("Ba'da Maghrib", $agenda->time_label);
        $this->assertSame('Ustadz Fulan, Lc.', $agenda->speaker);
        $this->assertSame("Setiap Kamis, Ba'da Maghrib", $agenda->scheduleLabel());
    }

    public function test_a_weekly_agenda_needs_a_day_but_no_date_or_time(): void
    {
        $this->actingAs($this->user)
            ->post(route('organizations.agendas.store', $this->organization), [
                'title' => 'Kajian Tafsir',
                'schedule' => 'weekly',
                'status' => 'published',
            ])
            ->assertSessionHasErrors('day_of_week')
            ->assertSessionDoesntHaveErrors(['starts_at', 'time_label']);
    }

    public function test_a_one_off_agenda_still_needs_a_date(): void
    {
        $this->actingAs($this->user)
            ->post(route('organizations.agendas.store', $this->organization), [
                'title' => 'Musyawarah Cabang',
                'schedule' => 'once',
                'status' => 'published',
            ])
            ->assertSessionHasErrors('starts_at')
            ->assertSessionDoesntHaveErrors('day_of_week');
    }

    public function test_switching_a_weekly_agenda_back_to_one_off_clears_the_flag(): void
    {
        $agenda = Agenda::factory()->weekly()->published()->create([
            'organization_id' => $this->organization->id,
            'time_label' => "Ba'da Isya",
        ]);

        $this->actingAs($this->user)
            ->patch(route('organizations.agendas.update', [$this->organization, $agenda]), [
                'title' => $agenda->title,
                'schedule' => 'once',
                'starts_at' => '2026-12-01T08:00',
                'status' => 'published',
            ])
            ->assertSessionHasNoErrors();

        $agenda->refresh();

        $this->assertFalse($agenda->repeats_weekly);
        $this->assertNull($agenda->time_label);
        $this->assertSame('2026-12-01 08:00', $agenda->starts_at->format('Y-m-d H:i'));
    }

    public function test_the_next_occurrence_is_the_coming_matching_weekday(): void
    {
        $agenda = Agenda::factory()->weekly()->make(['starts_at' => '2026-01-01']); // a Thursday

        Carbon::setTestNow('2026-10-29 21:00'); // a Thursday: today still counts, all day
        $this->assertSame('2026-10-29', $agenda->nextOccurrence()->toDateString());

        Carbon::setTestNow('2026-10-30 08:00'); // Friday: next Thursday
        $this->assertSame('2026-11-05', $agenda->nextOccurrence()->toDateString());
    }

    public function test_a_weekly_agenda_without_a_time_shows_just_the_day(): void
    {
        $agenda = Agenda::factory()->weekly()->make(['starts_at' => '2026-01-01', 'time_label' => null]);

        $this->assertSame('Setiap Kamis', $agenda->scheduleLabel());
        $this->assertNull($agenda->sectionItem()['time']);
    }

    public function test_the_section_shows_the_weekday_and_speaker_instead_of_a_date(): void
    {
        Agenda::factory()->weekly()->published()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Kajian Ahad Pagi',
            'starts_at' => '2026-01-04', // a Sunday
            'time_label' => '05.30 - selesai',
            'speaker' => 'Ustadz Fulan, Lc.',
        ]);

        $this->actingAs($this->user)
            ->get(route('organizations.builder.canvas', [$this->organization, $this->page]))
            ->assertOk()
            ->assertSee('Kajian Ahad Pagi')
            ->assertSee('Setiap')
            ->assertSee('Ahad')
            ->assertSee('05.30 - selesai')
            ->assertSee('Narasumber: Ustadz Fulan, Lc.')
            // The anchor date must never leak out as if it were the event's date.
            ->assertDontSee('2026 &middot;', false);
    }

    public function test_weekly_agendas_are_listed_before_dated_ones(): void
    {
        Agenda::factory()->published()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Acara Bertanggal',
            'starts_at' => now()->addDay(),
        ]);
        Agenda::factory()->weekly()->published()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Kajian Rutin',
            'starts_at' => now()->subYear(),
        ]);

        $this->assertSame(['Kajian Rutin', 'Acara Bertanggal'], $this->organization->agendas()->pluck('title')->all());
    }
}
