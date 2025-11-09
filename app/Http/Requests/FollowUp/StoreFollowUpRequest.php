<?php

namespace App\Http\Requests\FollowUp;

use Illuminate\Foundation\Http\FormRequest;

class StoreFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization done in controller/policy if needed
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id'   => 'required|exists:clients,id',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'due_date'    => 'required|date',
            'notes'       => 'sometimes|nullable|string',
            'priority'    => 'sometimes|in:low,normal,high',
        ];
    }
}
