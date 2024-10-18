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
        return [
            'name' => ['required', 'string', 'min:3', 'max:25'],
            // 'email' => 'required|email|unique:customers,email',
            //  'password' => ['required', 'min:8', 'confirmed'],
            'mikrotik_id' => 'required',
            'paket_id' => 'required',
            'ppp_type_id' => 'required',
            'discount' => ['numeric', 'nullable'],
            'contact' => 'digits_between:10,15|nullable',

        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required',
            // 'password.confirmed' => 'The password field must match confirm password.',
            'mikrotik_id.required' => 'Server is required',
            'paket_id.required' => 'Paket is required',
            'ppp_type_id.required' => 'Service is required',
        ];
    }

    protected function prepareForValidation()
    {
        // $this->request->remove('password_confirmation');
    }
}
