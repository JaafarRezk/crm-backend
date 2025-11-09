<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // filters may include status, assigned_to, search, date_from/date_to etc.
            'status' => 'nullable|string',
            'assigned_to' => 'nullable|integer|exists:users,id',
            'search' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'format' => 'nullable|in:csv,xlsx', // support csv by default
        ];
    }
}
