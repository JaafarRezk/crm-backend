<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $clientId = $this->route('client')?->id ?? null;

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes','nullable','email', Rule::unique('clients', 'email')->ignore($clientId)],
            'phone' => 'sometimes|nullable|string|max:20',
            'status' => 'sometimes|in:New,Active,Hot,Inactive',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
        ];
    }
}
