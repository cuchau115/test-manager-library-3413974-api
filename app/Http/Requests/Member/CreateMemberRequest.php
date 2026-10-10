<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class CreateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:200', 'min:2'],
            'document_number' => ['required', 'string', 'max:30', 'min:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'El nombre completo es obligatorio.',
            'full_name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'document_number.required' => 'El número de documento es obligatorio.',
            'document_number.min' => 'El documento debe tener al menos 3 caracteres.',
        ];
    }
}