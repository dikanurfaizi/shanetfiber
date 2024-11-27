<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $internet_service = $this->input('internet_service');
        $routerboardId = $this->input('mikrotik_id');
        if ($internet_service == 'ppp') {
            return [
                'name' => ['required', 'string', 'min:3', 'max:25'],
                // 'email' => 'required|email|unique:customers,email',
                //  'password' => ['required', 'min:8', 'confirmed'],
                'mikrotik_id' => 'required',
                'paket_id' => 'required',
                'internet_service' => 'required',
                'ppp_type_id' => 'required',
                'discount' => ['numeric', 'nullable'],
                'contact' => 'digits_between:10,15|nullable',

            ];
        } else if ($internet_service == 'ip_static') {
            return [
                'name' => ['required', 'string', 'min:3', 'max:25'],
                // 'email' => 'required|email|unique:customers,email',
                //  'password' => ['required', 'min:8', 'confirmed'],
                'mikrotik_id' => 'required',
                'paket_id' => 'required',
                'internet_service' => 'required',
                'interface' => 'required',
                'ip_static' => 'required|unique:customers,ip_static,null,null,mikrotik_id,' . $routerboardId,
                'mac_address' => 'required|unique:customers,mac_address,null,null,mikrotik_id,' . $routerboardId,
                'discount' => ['numeric', 'nullable'],
                'contact' => 'digits_between:10,15|nullable',

            ];
        } else {
            return [
                'mikrotik_id' => 'required',
                'paket_id' => 'required',
                'internet_service' => 'required',
            ];
        }
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

    protected function prepareForValidation()
    {
        // $this->request->remove('password_confirmation');
    }
}
