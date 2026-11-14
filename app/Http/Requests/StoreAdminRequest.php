<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                function ($attribute, $value, $fail) {
                    // 1. Validasi Angka Berurutan (3 digit atau lebih, misal: 123, 456, 987)
                    if (preg_match('/(012|123|234|345|456|567|678|789|987|876|765|654|543|432|321|210)/', $value)) {
                        $fail(__('install.validation.password_sequential'));
                    }

                    // 2. Validasi Format Tanggal (8 digit angka berurutan yang menyerupai ddmmyyyy atau yyyymmdd)
                    // Pola mendeteksi 8 digit angka murni
                    if (preg_match('/\d{8}/', $value)) {
                        $fail(__('install.validation.password_date_format'));
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('install.validation.name_required'),
            'email.required' => __('install.validation.email_required'),
            'email.email' => __('install.validation.email_invalid'),
            'email.unique' => __('install.validation.email_unique'),
            'password.required' => __('install.validation.password_required'),
            'password.min' => __('install.validation.password_min'),
            'password.confirmed' => __('install.validation.password_confirmed'),
        ];
    }
}
