<?php

namespace App\Http\Requests\Loan;

use Illuminate\Foundation\Http\FormRequest;

class CreateLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'uuid', 'exists:library.member,id'],
            'items' => ['required', 'array', 'min:1', 'max:3'],
            'items.*.book_id' => ['required', 'uuid', 'exists:library.book,id'],
            'loaned_at' => ['nullable', 'date_format:Y-m-d\TH:i:s\Z'],
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required' => 'El socio es obligatorio.',
            'member_id.exists' => 'El socio no existe.',
            'items.required' => 'Debe especificar al menos un libro.',
            'items.min' => 'Debe especificar al menos un libro.',
            'items.max' => 'No puede especificar más de 3 libros.',
            'items.*.book_id.required' => 'El ID del libro es obligatorio.',
            'items.*.book_id.exists' => 'El libro no existe.',
            'loaned_at.date_format' => 'El formato de fecha debe ser ISO-8601 (YYYY-MM-DDTHH:MM:SSZ).',
        ];
    }
}