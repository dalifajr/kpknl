<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRisalahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'no_risalah' => ['required', 'string', 'max:50'],
            'jenis' => ['required', 'string', Rule::in(['minuta', 'tap', 'batal'])],
            'tgl_risalah' => ['required', 'date'],
            'pemohon_lelang' => ['required', 'string', 'max:100'],
            'link_erisalah' => ['nullable', 'string'],
            'box' => ['nullable', 'string', 'max:50'],
            'lemari' => ['nullable', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_risalah.required' => 'Nomor risalah wajib diisi.',
            'jenis.required' => 'Jenis risalah wajib dipilih.',
            'jenis.in' => 'Jenis risalah harus berupa Minuta, TAP, atau Batal.',
            'tgl_risalah.required' => 'Tanggal risalah wajib diisi.',
            'tgl_risalah.date' => 'Format tanggal risalah tidak valid.',
            'pemohon_lelang.required' => 'Pemohon lelang wajib diisi.',
        ];
    }
}
