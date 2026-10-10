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
            'cover' => ['required', 'file', 'max:2048', 'mimes:jpg,jpeg,png'],
        ];
    }

    public function messages(): array
    {
        return [
            'cover.required' => 'La portada es obligatoria.',
            'cover.file' => 'El archivo debe ser una imagen.',
            'cover.max' => 'La imagen no puede superar 2 MB.',
            'cover.mimes' => 'Solo se permiten imágenes JPG o PNG.',
        ];
    }
}