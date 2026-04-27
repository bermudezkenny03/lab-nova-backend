<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

// Form request for validating bulk assignment of permissions to roles
class BulkAssignPermissionsRequest extends FormRequest
{
    // Authorize all users to make this request (you can add specific authorization logic if needed)
    public function authorize(): bool
    {
        return true;
    }
    // Define validation rules for bulk assigning permissions to roles
    public function rules(): array
    {
        return [
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
            'modules' => 'required|array',
            'modules.*.module_id' => 'required|exists:modules,id',
            'modules.*.permission_ids' => 'required|array',
            'modules.*.permission_ids.*' => 'exists:permissions,id',
        ];
    }
}
