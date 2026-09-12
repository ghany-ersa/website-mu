<?php

namespace Tests\Feature;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationPage;
use App\Models\OrganizationSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the akad-venue section (formerly `sewa-aula`) against the `facilities` shape that took it down.
 *
 * The field is a list, but the builder's properties panel had no editor for it, so it fell
 * through to the generic single-line text input and saved a bare string. The section renders
 * that field with foreach(), so one save from the panel was enough to 500 both the public page
 * and the builder canvas that previews it.
 */
class AkadVenueSectionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    private OrganizationPage $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create(['whatsapp' => '6281234567890']);
        $this->organization->members()->attach($this->user->id, ['role' => OrganizationRole::Owner->value]);
        $this->page = OrganizationPage::factory()->create([
            'organization_id' => $this->organization->id,
            'slug' => 'akad-venue',
        ]);
    }

    private function renderWith(mixed $facilities): string
    {
        OrganizationSection::query()->where('organization_page_id', $this->page->id)->delete();

        $this->page->sections()->create([
            'key' => 'akad-venue',
            'variant' => 'nurul-huda',
            'content' => ['facilities' => $facilities],
            'order' => 0,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('organizations.builder.canvas', [$this->organization, $this->page]));

        $response->assertOk();

        return $response->getContent();
    }

    public function test_it_survives_facilities_saved_as_a_bare_string(): void
    {
        // The exact content that produced the 500 in production.
        $html = $this->renderWith('a');

        $this->assertStringContainsString('>a<', $html);
    }

    public function test_a_multiline_string_becomes_one_row_per_line(): void
    {
        $html = $this->renderWith("Sound system\nArea parkir luas");

        $this->assertStringContainsString('Sound system', $html);
        $this->assertStringContainsString('Area parkir luas', $html);
    }

    public function test_a_proper_list_renders_one_row_each(): void
    {
        $html = $this->renderWith(['Hingga 150 Tamu', 'Wudhu Terpisah']);

        $this->assertStringContainsString('Hingga 150 Tamu', $html);
        $this->assertStringContainsString('Wudhu Terpisah', $html);
        // One tick per facility.
        $this->assertSame(2, substr_count($html, 'text-emerald-300'));
    }

    public function test_an_empty_list_renders_no_facility_rows(): void
    {
        $html = $this->renderWith([]);

        $this->assertSame(0, substr_count($html, 'text-emerald-300'));
    }

    /**
     * The view's normalisation is a safety net; this is the actual fix - the panel must offer a
     * list editor so a save can't write a string in the first place.
     */
    public function test_the_builder_panel_offers_a_list_editor_for_facilities(): void
    {
        $this->page->sections()->create([
            'key' => 'akad-venue',
            'variant' => 'nurul-huda',
            'content' => ['facilities' => ['Sound system']],
            'order' => 0,
        ]);

        $panel = $this->actingAs($this->user)
            ->get(route('organizations.builder.page', [$this->organization, $this->page]))
            ->getContent();

        $this->assertStringContainsString('content[facilities][${index}]', $panel);
        $this->assertStringContainsString('+ Tambah fasilitas', $panel);
        // The generic single-line input that caused the corruption must not be what renders.
        $this->assertStringNotContainsString('name="content[facilities]" value="Sound system"', $panel);
    }

    /**
     * Many masjid deliberately avoid rental wording for a place of worship - "sewa" frames the
     * use of religious space as a commercial transaction - and ask for a voluntary infak
     * instead. The section's own defaults must carry that framing, since most takmir will never
     * edit them.
     */
    public function test_default_copy_avoids_rental_wording_and_offers_the_infak_framing(): void
    {
        $html = $this->renderWith(config('page-builder.sections.akad-venue.defaults')['facilities'] ?? []);

        $this->assertStringContainsString('Ajukan Penggunaan', $html);
        $this->assertStringNotContainsString('Ajukan Rencana Sewa', $html);
        $this->assertStringNotContainsString('Sewa Aula Serbaguna', $html);
    }

    public function test_the_infak_note_renders_and_is_omitted_when_blank(): void
    {
        OrganizationSection::query()->where('organization_page_id', $this->page->id)->delete();

        $section = $this->page->sections()->create([
            'key' => 'akad-venue',
            'variant' => 'nurul-huda',
            'content' => config('page-builder.sections.akad-venue.defaults'),
            'order' => 0,
        ]);

        $withNote = $this->actingAs($this->user)
            ->get(route('organizations.builder.canvas', [$this->organization, $this->page]));
        $withNote->assertOk();
        $withNote->assertSee('berinfak semampunya', false);

        // A masjid that does charge a fixed fee clears the field; the block must disappear
        // rather than fall back to wording the takmir deliberately removed.
        $section->update(['content' => array_merge($section->content, ['infak_note' => ''])]);

        $without = $this->actingAs($this->user)
            ->get(route('organizations.builder.canvas', [$this->organization, $this->page]));
        $without->assertOk();
        $without->assertDontSee('berinfak semampunya', false);
    }

    public function test_the_builder_panel_exposes_the_infak_note_field(): void
    {
        $this->page->sections()->create([
            'key' => 'akad-venue',
            'variant' => 'nurul-huda',
            'content' => config('page-builder.sections.akad-venue.defaults'),
            'order' => 0,
        ]);

        $panel = $this->actingAs($this->user)
            ->get(route('organizations.builder.page', [$this->organization, $this->page]))
            ->getContent();

        $this->assertStringContainsString('content[infak_note]', $panel);
        // Labelled in Indonesian rather than the raw field name ("Infak note").
        $this->assertStringContainsString('Catatan Infak', $panel);
    }
}
