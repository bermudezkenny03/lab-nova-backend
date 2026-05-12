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
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'equipment_status_id' => 'sometimes|exists:equipment_statuses,id',
            'name' => 'sometimes|string|max:150',
            'description' => 'sometimes|nullable|string',
            'stock' => 'sometimes|nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'equipment_images' => 'sometimes|array|min:1',
            'equipment_images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ];
    }
}