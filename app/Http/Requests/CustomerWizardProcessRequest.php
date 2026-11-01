<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerWizardProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Karena data wizard digabungkan dari session, kita ambil payload dari session untuk divalidasi.
     */
    public function validationData(): array
    {
        return array_merge(
            session('wizard.step1', []),
            session('wizard.step2', []),
            session('wizard.step3', []),
            ['addons' => session('wizard.step4', [])]
        );
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'min:3'],
            'email'      => ['required', 'email'],
            'contact'    => ['required', 'regex:/^62[0-9]{8,14}$/'],
            'user_id'    => ['required', 'exists:users,id'],
            'is_prorate' => ['required', 'boolean'],
            'latitude'   => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'  => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => __('customer.wizard.validation.name_required'),
            'name.min'           => __('customer.wizard.validation.name_min'),
            'email.required'     => __('customer.wizard.validation.email_required'),
            'email.email'        => __('customer.wizard.validation.email_invalid'),
            'contact.required'   => __('customer.wizard.validation.contact_required'),
            'contact.regex'      => __('customer.wizard.validation.contact_regex'),
            'user_id.required'   => __('customer.wizard.validation.user_id_required'),
            'user_id.exists'     => __('customer.wizard.validation.user_id_exists'),
            'is_prorate.required' => __('customer.wizard.validation.is_prorate_required'),
            'is_prorate.boolean'  => __('customer.wizard.validation.is_prorate_in'),
            'latitude.numeric'   => __('customer.wizard.validation.latitude_numeric'),
            'latitude.between'   => __('customer.wizard.validation.latitude_between'),
            'longitude.numeric'  => __('customer.wizard.validation.longitude_numeric'),
            'longitude.between'  => __('customer.wizard.validation.longitude_between'),
        ];
    }
}
