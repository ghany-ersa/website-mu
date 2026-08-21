<?php

namespace App\Http\Requests;

use App\Models\Organization;
use App\Models\Template;
use App\Rules\NotTooLightColor;
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
     * Subdomains that must stay reserved for platform use and can't be claimed
     * by an organization (e.g. "admin.website-mu.test" would shadow our own panel).
     *
     * @var array<int, string>
     */
    private const RESERVED_SLUGS = [
        'www', 'admin', 'api', 'app', 'mail', 'ftp', 'localhost',
        'staging', 'dashboard', 'assets', 'static', 'cdn', 'support',
        'help', 'blog', 'docs', 'status', 'billing', 'auth', 'login',
        'register', 'website-mu',
    ];

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'organization_type_id' => ['required', 'exists:organization_types,id'],
            'template_id' => ['nullable', 'exists:templates,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'min:3',
                'max:63',
                'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                Rule::notIn(self::RESERVED_SLUGS),
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
            'slug.not_in' => 'Slug ini merupakan kata yang dicadangkan sistem, silakan gunakan slug lain.',
            'slug.unique' => 'Slug ini sudah digunakan oleh organisasi lain.',
            'slug.min' => 'Slug minimal terdiri dari :min karakter.',
            'slug.max' => 'Slug maksimal terdiri dari :max karakter.',
        ];
    }

    /**
     * Normalize the slug to lowercase before validation runs, since subdomains
     * are case-insensitive but the regex rule above only accepts lowercase.
     *
     * Also auto-fills template_id from the chosen organization_type_id when it isn't
     * already set (e.g. the user didn't arrive via TemplateUseController's "Gunakan
     * Template" flow). For this initial stage, the create form doesn't ask the user to
     * pick a template directly — each organization type has (at most) one active
     * template, so we pick it for them. See prd.md §24.4.
     *
     * Once a template is resolved, its brand colors (structure.brand.primary/secondary)
     * are copied into primary_color/secondary_color too — per prd.md §6, brand colors
     * should default to the chosen template's identity, while remaining editable later
     * via Brand Settings.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('slug')) {
            $this->merge(['slug' => strtolower((string) $this->input('slug'))]);
        }

        if (! $this->filled('template_id') && $this->filled('organization_type_id')) {
            $template = Template::where('organization_type_id', $this->input('organization_type_id'))
                ->where('is_active', true)
                ->first();

            if ($template) {
                $this->merge(['template_id' => $template->id]);
            }
        }

        if ($this->filled('template_id') && ! $this->filled('primary_color') && ! $this->filled('secondary_color')) {
            $brand = Template::find($this->input('template_id'))?->structure['brand'] ?? [];

            if ($brand) {
                $this->merge([
                    'primary_color' => $brand['primary'] ?? null,
                    'secondary_color' => $brand['secondary'] ?? null,
                ]);
            }
        }
    }
}
