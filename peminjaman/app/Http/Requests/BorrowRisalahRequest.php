<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BorrowRisalahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selected_items' => ['required', 'array', 'min:1'],
            'selected_items.*' => ['required', 'string'], // format "minuta_123", "tap_456", "batal_789"
            'alasan_peminjaman' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'selected_items.required' => 'Pilih minimal satu risalah yang akan dipinjam.',
            'selected_items.min' => 'Pilih minimal satu risalah yang akan dipinjam.',
            'alasan_peminjaman.required' => 'Alasan peminjaman wajib diisi.',
        ];
    }
}
