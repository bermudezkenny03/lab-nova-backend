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
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50|unique:equipments,code',
            'description' => 'nullable|string',
            'stock' => 'nullable|integer|min:0',
            'status' => 'in:available,maintenance,out_of_service',
            'is_active' => 'boolean',
            'equipment_images'     => 'required|array',
            'equipment_images.*'   => 'image|mimes:jpg,jpeg,png,webp|max:10240',
        ];
    }
}
