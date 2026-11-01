<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerWizardStep2Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mikrotik_id'      => ['required', 'exists:mikrotiks,id'],
            'internet_service' => ['required', 'in:ppp,ip_static'],
            'ppp_type_id'      => ['required_if:internet_service,ppp', 'nullable', 'exists:ppp_types,id'],
            'interface'        => ['required_if:internet_service,ip_static', 'nullable', 'string'],
            'ip_static'        => ['required_if:internet_service,ip_static', 'nullable', 'ip'],
            'mac_address'      => ['required_if:internet_service,ip_static', 'nullable', 'string', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'mikrotik_id.required'      => __('customer.wizard.validation.mikrotik_id_required'),
            'mikrotik_id.exists'        => __('customer.wizard.validation.mikrotik_id_exists'),
            'internet_service.required' => __('customer.wizard.validation.internet_service_required'),
            'internet_service.in'       => __('customer.wizard.validation.internet_service_in'),
            'ppp_type_id.required_if'   => __('customer.wizard.validation.ppp_type_id_required_if'),
            'ppp_type_id.exists'        => __('customer.wizard.validation.ppp_type_id_exists'),
            'interface.required_if'     => __('customer.wizard.validation.interface_required_if'),
            'ip_static.required_if'     => __('customer.wizard.validation.ip_static_required_if'),
            'ip_static.ip'              => __('customer.wizard.validation.ip_static_ip'),
            'mac_address.required_if'   => __('customer.wizard.validation.mac_address_required_if'),
            'mac_address.regex'         => __('customer.wizard.validation.mac_address_regex'),
        ];
    }
}
