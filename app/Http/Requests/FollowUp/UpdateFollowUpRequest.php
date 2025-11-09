<?php

namespace App\Http\Requests\FollowUp;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization done in controller/policy
        return true;
    }

    public function rules(): array
    {
        return [
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'status'      => 'sometimes|in:pending,completed,cancelled',
            'due_date'     => 'sometimes|date',
            'notes'       => 'sometimes|nullable|string',
            'priority'    => 'sometimes|in:low,normal,high',
        ];
    }
}
