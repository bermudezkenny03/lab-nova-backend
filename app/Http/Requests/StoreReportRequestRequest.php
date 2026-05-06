<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:reservations,equipment_usage,user_activity',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'filters' => 'nullable|array',
        ];
    }
}
