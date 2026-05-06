<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'sometimes|string|max:150',
            'code' => 'sometimes|string|max:50',
            'description' => 'nullable|string',
            'stock' => 'nullable|integer|min:0',
            'status' => 'in:available,maintenance,out_of_service',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
