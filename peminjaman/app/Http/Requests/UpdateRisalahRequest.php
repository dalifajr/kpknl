<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRisalahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'no_risalah' => ['required', 'string', 'max:50'],
            'tgl_risalah' => ['required', 'date'],
            'tgl_validasi' => ['nullable', 'date'],
            'nama_pelelang' => ['required', 'string', 'max:100'],
            'pemohon_lelang' => ['required', 'string', 'max:100'],
            'link_erisalah' => ['nullable', 'string'],
            'box' => ['nullable', 'string', 'max:50'],
            'lemari' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:tersedia,sedang_dipinjam'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_risalah.required' => 'Nomor risalah wajib diisi.',
            'tgl_risalah.required' => 'Tanggal risalah wajib diisi.',
            'nama_pelelang.required' => 'Nama pelelang wajib diisi.',
            'pemohon_lelang.required' => 'Pemohon lelang wajib diisi.',
            'status.required' => 'Status risalah wajib dipilih.',
        ];
    }
}
