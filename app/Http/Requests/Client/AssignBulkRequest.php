<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class AssignBulkRequest extends FormRequest
{
    public function authorize(): bool { return true; } // check role in controller/service

    public function rules(): array
    {
        return [
            'client_ids' => 'required|array|min:1',
            'client_ids.*' => 'integer|exists:clients,id',
            'assigned_to' => 'required|exists:users,id',
        ];
    }
}


