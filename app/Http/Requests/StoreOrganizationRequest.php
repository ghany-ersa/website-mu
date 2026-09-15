<?php

namespace App\Http\Requests;

use App\Models\Organization;
use App\Models\Template;
use App\Rules\NotTooLightColor;
use App\Rules\ReservedSlug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrganizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Organization::class);
    }

    /**
     * organization_type_id is listed even though the form never posts it: prepareForValidation()
     * derives it from the chosen template, and keeping a rule for it is what lets it through
     * validated() into the insert — plus it turns a failed derivation into a validation error
     * rather than a NOT NULL violation from the database.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'organization_type_id' => ['required', 'exists:organization_types,id'],
            'template_id' => [
                'required',
                Rule::exists('templates', 'id')->where('is_active', true),
                Rule::notIn($this->exclusiveTemplateIds()),
                Rule::notIn($this->typelessTemplateIds()),
            ],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'min:3',
                'max:63',
                'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                new ReservedSlug,
                Rule::unique('organizations', 'slug'),
            ],
            'region' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'primary_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/', new NotTooLightColor],
            'secondary_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/', new NotTooLightColor],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung (tidak di awal/akhir atau berurutan).',
            'slug.unique' => 'Slug ini sudah digunakan oleh organisasi lain.',
            'slug.min' => 'Slug minimal terdiri dari :min karakter.',
            'slug.max' => 'Slug maksimal terdiri dari :max karakter.',
            'template_id.required' => 'Pilih template terlebih dahulu.',
            'template_id.exists' => 'Template yang dipilih tidak tersedia.',
            'template_id.not_in' => in_array((int) $this->input('template_id'), $this->typelessTemplateIds(), true)
                ? 'Template ini belum bisa dipakai karena belum punya jenis organisasi. Hubungi admin.'
                : 'Template ini eksklusif untuk paket Professional. Organisasi baru selalu dimulai dari paket Starter - upgrade paket lalu ganti template dari halaman pengaturan organisasi.',
        ];
    }

    /**
     * Every organization is created on the Starter plan (see OrganizationController::store()),
     * so a Template::is_exclusive template can never be legitimately chosen at creation time —
     * not via the template picker (which renders them locked), and not via a template_id
     * smuggled in from TemplateUseController's "Gunakan Template" flow either. The only path
     * onto an exclusive template is switching after upgrading (see OrganizationTemplateController).
     *
     * @return array<int, int>
     */
    private function exclusiveTemplateIds(): array
    {
        return Template::where('is_exclusive', true)->pluck('id')->all();
    }

    /**
     * Templates carrying no organization type, rejected because organizations.organization_type_id
     * is NOT NULL while templates.organization_type_id is nullable — and since prepareForValidation()
     * now derives the organization's type *from* the template, such a template would otherwise reach
     * the insert with a null type and fail as a raw SQL error instead of a readable message.
     *
     * OrganizationController::createTemplate() already hides these from the picker, so reaching this
     * rule means a hand-crafted request or a template an admin blanked out mid-flow.
     *
     * @return array<int, int>
     */
    private function typelessTemplateIds(): array
    {
        return Template::whereNull('organization_type_id')->pluck('id')->all();
    }

    /**
     * Normalize the slug to lowercase before validation runs, since subdomains
     * are case-insensitive but the regex rule above only accepts lowercase.
     *
     * Also derives organization_type_id *from* the chosen template, rather than the other way
     * around. The create form used to ask for the organization type and auto-pick a template
     * from it; that asked the user an abstract question ("what kind of organization are you?")
     * whose only real consequence — which template they got — they never saw. The flow now
     * opens with a visual template picker (OrganizationController::createTemplate()) and the
     * type falls out of that choice, so it is no longer a form field at all. The column stays
     * on organizations because the admin panel, the public catalog filter and the org badge
     * all still read it.
     *
     * Any organization_type_id posted alongside is discarded: trusting it would let a request
     * pair a template with a contradicting type. typelessTemplateIds() covers the one case this
     * derivation cannot satisfy — a template with no type of its own.
     *
     * Once a template is resolved, its brand colors (structure.brand.primary/secondary)
     * are copied into primary_color/secondary_color too - per prd.md §6, brand colors
     * should default to the chosen template's identity, while remaining editable later
     * via Brand Settings.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('slug')) {
            $this->merge(['slug' => strtolower((string) $this->input('slug'))]);
        }

        $template = $this->filled('template_id')
            ? Template::find($this->input('template_id'))
            : null;

        $this->merge(['organization_type_id' => $template?->organization_type_id]);

        if ($template && ! $this->filled('primary_color') && ! $this->filled('secondary_color')) {
            $brand = $template->structure['brand'] ?? [];

            if ($brand) {
                $this->merge([
                    'primary_color' => $brand['primary'] ?? null,
                    'secondary_color' => $brand['secondary'] ?? null,
                ]);
            }
        }
    }
}
