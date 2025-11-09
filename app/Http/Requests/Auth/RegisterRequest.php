<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Public endpoint - anyone can register (adjust if registration is restricted)
        return true;
    }

    public function prepareForValidation(): void
    {
        // Normalize phone (optional): trim and remove non-digits
        if ($this->has('phone')) {
            $phone = preg_replace('/\D+/', '', (string) $this->input('phone'));
            $this->merge(['phone' => $phone ?: null]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            // password_confirmation required when using confirmed
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8) // ->mixedCase()->numbers()->symbols() can be added
            ],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            // allowed values must match your users.user_type enum
            'user_type' => ['nullable', 'string', 'in:admin,manager,sales_rep'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.confirmed' => 'Password confirmation does not match.',
            'user_type.in' => 'Invalid user type.',
            'phone.unique' => 'Phone number is already taken.',
        ];
    }
}
