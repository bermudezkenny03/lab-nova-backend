<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

// Form request for validating user creation data
class StoreUserRequest extends FormRequest
{
    // Authorize all users to make this request (you can add specific authorization logic if needed)
    public function authorize(): bool
    {
        return true;
    }
    // Define validation rules for user creation
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50',
            'last_name' => 'required|string|max:60',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|boolean',
            'role_id' => 'required|exists:roles,id',
            'gender' => 'nullable|string|max:14',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string|max:100',
            'addon_address' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ];
    }
}
