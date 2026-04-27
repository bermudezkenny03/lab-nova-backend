<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

// Form request for validating permission updates for a module
class UpdateModulePermissionsRequest extends FormRequest
{
    // Authorize all users to make this request (you can add specific authorization logic if needed)
    public function authorize(): bool
    {
        return true;
    }
    // Define validation rules for updating module permissions
    public function rules(): array
    {
        return [
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ];
    }
}
