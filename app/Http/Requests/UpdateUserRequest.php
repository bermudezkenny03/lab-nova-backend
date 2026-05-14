<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Form request for validating user update data
class UpdateUserRequest extends FormRequest
{
    // Authorize all users to make this request (you can add specific authorization logic if needed)
    public function authorize(): bool
    {
        return true;
    }
    // Define validation rules for user update
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:50',
            'last_name' => 'sometimes|string|max:60',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($this->route('user'))],
            'password' => 'nullable|string|min:6',
            'phone' => 'sometimes|string|max:20',
            'status' => 'sometimes|boolean',
            'role_id' => 'sometimes|exists:roles,id',
            'gender_type_id' => 'nullable|exists:gender_types,id',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string|max:100',
            'addon_address' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ];
    }
}
