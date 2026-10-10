<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:120', 'min:3'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:librarian'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'El usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'role.required' => 'El rol es obligatorio.',
            'role.in' => 'El rol debe ser "librarian".',
        ];
    }
}