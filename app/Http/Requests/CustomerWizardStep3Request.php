<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerWizardStep3Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package_id'           => ['required', 'exists:packages,id'],
            'package_duration_id'  => ['required', 'exists:package_durations,id'],
            'member_id'            => ['nullable', 'exists:memberships,id'],
            'billing_option'       => ['required', 'in:monthly,one_time'],
            'billing_periode_name' => ['required_if:billing_option,one_time', 'nullable', 'string'],
            'is_prorate'           => ['required', 'in:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'package_id.required'           => __('customer.wizard.validation.package_id_required'),
            'package_id.exists'             => __('customer.wizard.validation.package_id_exists'),
            'package_duration_id.required'  => __('customer.wizard.validation.package_duration_id_required'),
            'package_duration_id.exists'    => __('customer.wizard.validation.package_duration_id_exists'),
            'member_id.exists'              => __('customer.wizard.validation.member_id_exists'),
            'billing_option.required'       => __('customer.wizard.validation.billing_option_required'),
            'billing_option.in'             => __('customer.wizard.validation.billing_option_in'),
            'billing_periode_name.required_if' => __('customer.wizard.validation.billing_periode_name_required_if'),
            'is_prorate.required'           => __('customer.wizard.validation.is_prorate_required'),
            'is_prorate.in'                 => __('customer.wizard.validation.is_prorate_in'),
        ];
    }
}
