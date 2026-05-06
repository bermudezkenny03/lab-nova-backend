<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reservation_id' => 'required|exists:reservations,id',
            'user_id' => 'nullable|exists:users,id',
            'action' => 'required|string|max:100',
            'description' => 'nullable|string',
        ];
    }
}
