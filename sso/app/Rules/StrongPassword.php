<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen($value) < 8) {
            $fail('Password harus memiliki minimal 8 karakter.');
            return;
        }

        if (strlen($value) > 128) {
            $fail('Password tidak boleh melebihi 128 karakter.');
            return;
        }

        if (!preg_match('/[A-Z]/', $value)) {
            $fail('Password harus mengandung minimal 1 huruf besar (A-Z).');
        }

        if (!preg_match('/[a-z]/', $value)) {
            $fail('Password harus mengandung minimal 1 huruf kecil (a-z).');
        }

        if (!preg_match('/[0-9]/', $value)) {
            $fail('Password harus mengandung minimal 1 angka (0-9).');
        }

        if (!preg_match('/[!@#$%^&*()\-_=+\\\|\[\]{};:\'",.<>?\/]/', $value)) {
            $fail('Password harus mengandung minimal 1 simbol / karakter khusus (!@#$%^&* dll).');
        }
    }
}
