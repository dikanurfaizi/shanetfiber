<?php

namespace App\Http\Requests;

use App\Helpers\FormatReceiver;
use Illuminate\Foundation\Http\FormRequest;

class CustomerWizardStep1Request extends FormRequest
{
    /**
     * Tentukan apakah user memiliki akses ke request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalisasi input sebelum validasi dijalankan (seperti contact/nomor WA).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('contact')) {
            $formatter = app(FormatReceiver::class);
            $this->merge([
                'contact' => $formatter->normalize($this->input('contact'))
            ]);
        }
    }

    /**
     * Aturan validasi untuk Wizard Step 1.
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'exists:users,id'
            ],
            'name' => [
                'required',
                'string',
                'min:3'
            ],
            'email' => [
                'required',
                'email'
            ],
            'contact' => [
                'required',
                'regex:/^62[0-9]{8,14}$/',
                'unique:customers,contact'
            ],
            'address' => [
                'nullable',
                'string',
                'max:255'
            ],
            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90'
            ],
            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180'
            ],
            'password' => [
                'nullable',
                'min:8',
                'confirmed'
            ]
        ];
    }

    /**
     * Pesan error terjemahan (Internationalization).
     */
    public function messages(): array
    {
        return [
            'user_id.required'   => __('customer.wizard.validation.user_id_required'),
            'user_id.exists'     => __('customer.wizard.validation.user_id_exists'),

            'name.required'      => __('customer.wizard.validation.name_required'),
            'name.min'           => __('customer.wizard.validation.name_min'),

            'email.required'     => __('customer.wizard.validation.email_required'),
            'email.email'        => __('customer.wizard.validation.email_invalid'),

            'contact.required'   => __('customer.wizard.validation.contact_required'),
            'contact.regex'      => __('customer.wizard.validation.contact_regex'),
            'contact.unique'     => __('customer.wizard.validation.contact_unique'),

            'latitude.numeric'   => __('customer.wizard.validation.latitude_numeric'),
            'latitude.between'   => __('customer.wizard.validation.latitude_between'),

            'longitude.numeric'  => __('customer.wizard.validation.longitude_numeric'),
            'longitude.between'  => __('customer.wizard.validation.longitude_between'),

            'password.min'       => __('customer.wizard.validation.password_min'),
            'password.confirmed' => __('customer.wizard.validation.password_confirmed'),
        ];
    }
}
