<?php

namespace App\Http\Requests\Communication;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'sometimes|required|in:call,email,meeting',
            'date' => 'sometimes|nullable|date',
            'notes' => 'sometimes|nullable|string',
        ];
    }
}
