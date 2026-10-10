<?php

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:250'],
            'author' => ['required', 'string', 'max:200'],
            'isbn' => ['required', 'string', 'size:13', 'regex:/^\d{13}$/'],
            'category_id' => ['required', 'uuid', 'exists:category,id'],
            'total_copies' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'author.required' => 'El autor es obligatorio.',
            'isbn.required' => 'El ISBN es obligatorio.',
            'isbn.size' => 'El ISBN debe tener 13 dígitos.',
            'isbn.regex' => 'El ISBN debe contener solo dígitos.',
            'category_id.required' => 'La categoría es obligatoria.',
            'category_id.exists' => 'La categoría no existe.',
            'total_copies.required' => 'El total de ejemplares es obligatorio.',
            'total_copies.min' => 'Debe haber al menos 1 ejemplar.',
        ];
    }
}