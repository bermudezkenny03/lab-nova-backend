<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'nullable|exists:categories,id',
            'equipment_status_id' => 'required|exists:equipment_statuses,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'equipment_images' => 'required|array|min:1',
            'equipment_images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
        ];
    }
}