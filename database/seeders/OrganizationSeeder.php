<?php

namespace Database\Seeders;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use App\Services\Samples\KlinikAisyiyahAmbuluSamples;
use App\Services\Samples\PcaAmbuluSamples;
use App\Services\Samples\PcmAmbuluSamples;
use App\Services\Samples\SuaraMuhammadiyahAmbuluSamples;
use Illuminate\Database\Seeder;

/**
 * Seeds one dummy organization per template (see TemplateSeeder) so staging has representative
 * data to test against without anyone manually clicking through "create organization" a dozen
 * times. Temporarily reduced to the Klinik Pratama Aisyiyah Ambulu and Suara Muhammadiyah
 * Ambulu showcases while template/org sample data is rebuilt from scratch, organization type
 * by organization type - see DatabaseSeeder and TemplateSeeder's doc comments. Reintroduce
 * more entries here as more templates are redone.
 *
 * Every organization is owned by the same user (admin@website-mu.id) so all dummy orgs are
 * reachable from one login without switching accounts.
 *
 * Relies on Organization::ensureHomePageExists() - same path OrganizationBuilderController::edit()
 * uses on first visit - to clone the template's pages/sections, which in turn triggers
 * CmsSampleDataSeeder for every CMS-backed section, so each dummy org also gets sample posts,
 * agendas, officers, etc. already in place.
 */
class OrganizationSeeder extends Seeder
{
    /**
     * Real contact details for the showcase organizations, keyed by a `contact` tag on the
     * specs below. Sections like formulir-kontak/donasi-zakat-infak fall back to the
     * organization's own `whatsapp` when a section's `wa_number` is blank, and header/footer
     * render `address`/`instagram_url`, so an organization without these renders half-empty
     * contact blocks - fine for a placeholder org, wrong for one meant to demo a finished site.
     *
     * @var array<string, array<string, string>>
     */
    private const CONTACTS = [
        'pcm-ambulu' => [
            'whatsapp' => PcmAmbuluSamples::WHATSAPP,
            'phone' => PcmAmbuluSamples::WHATSAPP,
            'address' => PcmAmbuluSamples::ADDRESS,
        ],
        'pca-ambulu' => [
            'whatsapp' => PcaAmbuluSamples::WHATSAPP,
            'phone' => PcaAmbuluSamples::WHATSAPP,
            'address' => PcaAmbuluSamples::ADDRESS,
        ],
        'klinik' => [
            'whatsapp' => KlinikAisyiyahAmbuluSamples::WHATSAPP,
            'phone' => KlinikAisyiyahAmbuluSamples::WHATSAPP,
            'address' => KlinikAisyiyahAmbuluSamples::ADDRESS,
            'instagram_url' => KlinikAisyiyahAmbuluSamples::INSTAGRAM,
        ],
        'suara-muhammadiyah' => [
            'whatsapp' => SuaraMuhammadiyahAmbuluSamples::WHATSAPP,
            'phone' => SuaraMuhammadiyahAmbuluSamples::WHATSAPP,
            'instagram_url' => SuaraMuhammadiyahAmbuluSamples::INSTAGRAM,
            'tiktok_url' => SuaraMuhammadiyahAmbuluSamples::TIKTOK,
        ],
    ];

    public function run(): void
    {
        $plans = Plan::whereIn('key', ['starter', 'organization', 'professional'])->get()->keyBy('key');

        $organizations = [
            // On the `organization` plan, not `professional` like the two exclusive showcases
            // below - this template is non-exclusive and the whole point of the showcase is to
            // demo what a cabang on a standard paid plan actually receives, limits included.
            ['template' => PcmAmbuluTemplateSeeder::SLUG, 'name' => 'PCM Ambulu', 'region' => 'Jember, Jawa Timur', 'plan' => 'organization', 'published' => true, 'contact' => 'pcm-ambulu'],
            // On `starter`, the cheapest plan and the one PcaAmbuluTemplateSeeder is designed
            // for - so this showcase doubles as the live check that a Starter cabang really
            // does keep a coherent site after CmsSampleDataSeeder truncates its sample lists
            // to that plan's quotas (officers 3, programs 3, agendas 3, gallery 3).
            ['template' => PcaAmbuluTemplateSeeder::SLUG, 'name' => 'PCA Ambulu', 'region' => 'Jember, Jawa Timur', 'plan' => 'starter', 'published' => true, 'contact' => 'pca-ambulu'],
            ['template' => KlinikAisyiyahAmbuluTemplateSeeder::SLUG, 'name' => 'Klinik Pratama Aisyiyah Ambulu', 'region' => 'Jember, Jawa Timur', 'plan' => 'professional', 'published' => true, 'contact' => 'klinik'],
            ['template' => SuaraMuhammadiyahAmbuluTemplateSeeder::SLUG, 'name' => 'Suara Muhammadiyah Ambulu', 'region' => 'Jember, Jawa Timur', 'plan' => 'professional', 'published' => true, 'contact' => 'suara-muhammadiyah'],
        ];

        foreach ($organizations as $spec) {
            $template = Template::where('slug', $spec['template'])->first();

            if (! $template) {
                continue;
            }

            $slug = str($spec['name'])->slug();

            $owner = User::firstOrCreate(
                ['email' => 'admin@website-mu.id'],
                [
                    'name' => 'Admin Website-Mu',
                    'password' => bcrypt('Passwordmu123!'),
                    'email_verified_at' => now(),
                ]
            );

            $organization = Organization::updateOrCreate(
                ['slug' => $slug],
                [
                    'organization_type_id' => $template->organization_type_id,
                    'template_id' => $template->id,
                    'plan_id' => $plans[$spec['plan']]->id,
                    'name' => $spec['name'],
                    'region' => $spec['region'],
                    'description' => $template->description,
                    'status' => $spec['published'] ? OrganizationStatus::Published : OrganizationStatus::Draft,
                    'published_at' => $spec['published'] ? now() : null,
                    ...$this->brandFrom($template),
                    ...self::CONTACTS[$spec['contact'] ?? ''] ?? [],
                ]
            );

            $organization->members()->syncWithoutDetaching([
                $owner->id => ['role' => OrganizationRole::Owner->value],
            ]);

            $organization->ensureHomePageExists();
        }
    }

    /**
     * Copy the template's brand identity onto the organization, the same way
     * TemplateSandboxService::forTemplate() and StoreOrganizationRequest do when a real user
     * picks a template. Without this the dummy orgs all rendered in the platform default
     * blue/green regardless of their template, so an exclusive template's distinct palette
     * (and its serif font / sharp radius) never showed up in the seeded showcase.
     *
     * @return array<string, string|null>
     */
    private function brandFrom(Template $template): array
    {
        $brand = $template->structure['brand'] ?? [];

        return [
            'primary_color' => $brand['primary'] ?? null,
            'secondary_color' => $brand['secondary'] ?? null,
            'font_family' => $brand['font'] ?? null,
            'border_radius' => $brand['radius'] ?? null,
        ];
    }
}
