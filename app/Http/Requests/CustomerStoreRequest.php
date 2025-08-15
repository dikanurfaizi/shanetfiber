<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $internet_service = $this->input('internet_service');
        $routerboardId = $this->input('mikrotik_id');

        $rules = [
            'mikrotik_id' => 'required',
            'paket_id' => 'required|exists:pakets,id',
            'internet_service' => 'required|in:ppp,ip_static',
            'discount' => ['numeric', 'nullable'],
            'contact' => 'nullable|digits_between:10,15',
            'name' => ['required', 'string', 'min:3', 'max:25'],
            'email' => 'required|email|unique:customers,email',
            'password' => 'nullable|min:8',
            'address' => 'nullable|string|max:255',
            'user_id' => 'required|exists:users,id',
            'member_id' => 'nullable|exists:members,id',
            'billing_option' => 'nullable|string',
        ];

        if ($internet_service === 'ppp') {
            $rules = array_merge($rules, [
                'ppp_type_id' => 'required|exists:ppp_types,id',
            ]);
        } elseif ($internet_service === 'ip_static') {
            $rules = array_merge($rules, [
                'interface' => 'required|string|max:50',
                'ip_static' => 'required|unique:customers,ip_static,null,null,mikrotik_id,' . $routerboardId,
                'mac_address' => 'required|unique:customers,mac_address,null,null,mikrotik_id,' . $routerboardId,
            ]);
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required',
            // 'password.confirmed' => 'The password field must match confirm password.',
            'mikrotik_id.required' => 'Server is required',
            'paket_id.required' => 'Paket is required',
            'ppp_type_id.required' => 'PPP type is required',
            'ip_static.required' => 'IP Address Static is required',
            'mac_address.required' => 'MAC Address is required',
        ];
    }
}
