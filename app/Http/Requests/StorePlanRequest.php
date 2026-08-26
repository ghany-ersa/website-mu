<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('plans', 'key')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price_monthly' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'limits' => ['required', 'array'],
            'limits.*' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
