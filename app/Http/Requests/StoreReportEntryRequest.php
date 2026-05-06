<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_request_id' => 'required|exists:report_requests,id',
            'file_path' => 'required|string|max:255',
            'file_name' => 'nullable|string|max:150',
            'file_type' => 'nullable|string|max:20',
            'generated_at' => 'nullable|date',
        ];
    }
}
