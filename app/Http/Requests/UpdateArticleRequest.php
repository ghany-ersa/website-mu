<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArticleRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('articles', 'slug')->ignore($this->route('article'))],
            'category' => ['nullable', 'string', 'max:100'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'body' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published'],
        ];
    }
}
