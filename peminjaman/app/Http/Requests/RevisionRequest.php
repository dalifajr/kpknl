<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'catatan' => ['nullable', 'string'],
            'catatan_no' => ['nullable', 'string'],
            'catatan_jenis' => ['nullable', 'string'],
            'catatan_tgl' => ['nullable', 'string'],
            'catatan_pelelang' => ['nullable', 'string'],
            'catatan_pemohon' => ['nullable', 'string'],
        ];
    }
}
