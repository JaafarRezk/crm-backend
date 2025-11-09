<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin();
    }
    // AdminUpdateUserRequest.php
    public function rules(): array
    {
        $routeParam = $this->route('user');
        $id = is_object($routeParam) ? ($routeParam->id ?? $routeParam) : $routeParam;

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'user_type' => 'sometimes|required|in:admin,manager,sales_rep',
            'phone' => 'sometimes|nullable|string|max:20',
        ];
    }
}
