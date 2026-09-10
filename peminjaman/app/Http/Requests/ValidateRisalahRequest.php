<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateRisalahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'box' => ['nullable', 'string', 'max:50'],
            'lemari' => ['nullable', 'string', 'max:50'],
            'tgl_validasi' => ['required', 'date'],
            'link_erisalah' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'tgl_validasi.required' => 'Tanggal validasi wajib diisi.',
            'tgl_validasi.date' => 'Format tanggal validasi tidak valid.',
        ];
    }
}
