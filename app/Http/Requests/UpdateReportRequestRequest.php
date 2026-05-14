<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_request_status_id' => 'sometimes|exists:report_request_statuses,id',
            'report_request_type_id' => 'sometimes|exists:report_request_types,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'filters' => 'nullable|array',
        ];
    }
}
