<?php

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:250'],
            'author' => ['sometimes', 'required', 'string', 'max:200'],
            'category_id' => ['sometimes', 'required', 'uuid', 'exists:library.category,id'],
            'total_copies' => ['sometimes', 'required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'author.required' => 'El autor es obligatorio.',
            'category_id.required' => 'La categoría es obligatoria.',
            'category_id.exists' => 'La categoría no existe.',
            'total_copies.required' => 'El total de ejemplares es obligatorio.',
            'total_copies.min' => 'Debe haber al menos 1 ejemplar.',
        ];
    }
}