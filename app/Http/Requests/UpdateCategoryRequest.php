<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100',
            'slug' => 'sometimes|string|max:120',
            'description' => 'nullable|string',
            'status' => 'sometimes|boolean',
        ];
    }
}
