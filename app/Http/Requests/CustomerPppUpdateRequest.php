<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Customer;

class CustomerPppUpdateRequest extends FormRequest
{
    public function rules()
    {

        $customer = $this->route('customer');
        if ($customer->internet_service == 'ppp') {
            return [
                'username' => ['required', 'string', 'max:45', Rule::unique('customers')->ignore($customer->slug, 'slug')],
                'password_ppp' => ['nullable', 'string', 'min:8'],
                'ppp_type_id' => 'required',
                'caller_id' => ['nullable', 'string'],
                'save_with_last_caller_id' => ['nullable', 'string'],
                'last_caller_id' => ['nullable', 'string'],
                'dont_disconnect_client_button' => ['nullable', 'string']
            ];
        } else if ($customer->internet_service == 'ip_static') {
            return [
                'ip_static' => ['required', 'ip', Rule::unique('customers')->ignore($customer->slug, 'slug')],
                'mac_address' => ['required', 'mac_address'],
                'interface' => 'required'
            ];
        }
    }

    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->password_ppp == null) {
            $this->request->remove('password_ppp');
        }
    }
    public function messages()
    {
        return [
            'password_ppp.required' => 'Password PPP is required',
            'paket_id.required' => 'Paket is required',

        ];
    }
}
