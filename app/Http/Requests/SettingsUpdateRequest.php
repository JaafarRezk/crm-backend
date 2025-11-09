<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // or check admin
    }

    public function rules(): array
    {
        return [
            // expect payload like: { "site_name": "X", "notes": "..." }
        ];
    }

    public function validationData()
    {
        // allow any key/value; no strict rules — handled in service
        return $this->all();
    }
}
