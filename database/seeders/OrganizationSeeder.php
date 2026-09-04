<?php

namespace Database\Seeders;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds one dummy organization per template (see TemplateSeeder) so staging has representative
 * data to test against without anyone manually clicking through "create organization" a dozen
 * times: different organization types (PDM, school, clinic, mosque, Ortom, ...), different
 * plans (to exercise plan limits/component gating across tiers), and a mix of draft/published
 * so both builder and public-site views have something to look at.
 *
 * Every organization is owned by the same user (ghany@ghany.id) so all dummy orgs are reachable
 * from one login without switching accounts.
 *
 * Relies on Organization::ensureHomePageExists() - same path OrganizationBuilderController::edit()
 * uses on first visit - to clone the template's pages/sections, which in turn triggers
 * CmsSampleDataSeeder for every CMS-backed section, so each dummy org also gets sample posts,
 * agendas, officers, etc. already in place.
 */
class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $plans = Plan::whereIn('key', ['starter', 'organization', 'professional'])->get()->keyBy('key');

        $organizations = [
            ['template' => 'muhammadiyah', 'name' => 'PCM Ambulu', 'region' => 'Jember, Jawa Timur', 'plan' => 'organization', 'published' => true],
            ['template' => 'aisyiyah', 'name' => 'PCA Ambulu', 'region' => 'Jember, Jawa Timur', 'plan' => 'organization', 'published' => true],
            ['template' => 'aum-pendidikan', 'name' => 'SD Muhammadiyah 1 Ambulu', 'region' => 'Jember, Jawa Timur', 'plan' => 'professional', 'published' => true],
            ['template' => 'aum-kesehatan-sosial', 'name' => 'Klinik Muhammadiyah Sehati', 'region' => 'Jember, Jawa Timur', 'plan' => 'professional', 'published' => true],
            ['template' => 'aum-sosial', 'name' => 'Panti Asuhan Muhammadiyah Ambulu', 'region' => 'Jember, Jawa Timur', 'plan' => 'starter', 'published' => false],
            ['template' => 'masjid-mushola', 'name' => 'Masjid Al-Ikhlas Ambulu', 'region' => 'Jember, Jawa Timur', 'plan' => 'starter', 'published' => true],
            ['template' => 'pemuda-muhammadiyah', 'name' => 'Pemuda Muhammadiyah Ambulu', 'region' => 'Jember, Jawa Timur', 'plan' => 'starter', 'published' => false],
            ['template' => 'nasyiatul-aisyiyah', 'name' => 'Nasyiatul Aisyiyah Ambulu', 'region' => 'Jember, Jawa Timur', 'plan' => 'starter', 'published' => false],
            ['template' => 'imm', 'name' => 'IMM Komisariat Ambulu', 'region' => 'Jember, Jawa Timur', 'plan' => 'organization', 'published' => true],
            ['template' => 'ipm', 'name' => 'IPM Ambulu', 'region' => 'Jember, Jawa Timur', 'plan' => 'starter', 'published' => false],
            ['template' => 'tapak-suci', 'name' => 'Tapak Suci Putera Muhammadiyah Ambulu', 'region' => 'Jember, Jawa Timur', 'plan' => 'organization', 'published' => true],
            ['template' => 'hizbul-wathan', 'name' => 'Hizbul Wathan Qabilah Ambulu', 'region' => 'Jember, Jawa Timur', 'plan' => 'starter', 'published' => false],
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
                ]
            );

            $organization->members()->syncWithoutDetaching([
                $owner->id => ['role' => OrganizationRole::Owner->value],
            ]);

            $organization->ensureHomePageExists();
        }
    }
}
