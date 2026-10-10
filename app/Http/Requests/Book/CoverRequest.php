<?php

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;

class CoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'La portada es obligatoria.',
            'file.file' => 'El archivo debe ser una imagen.',
            'file.max' => 'La imagen no puede superar 2 MB.',
        ];
    }
}