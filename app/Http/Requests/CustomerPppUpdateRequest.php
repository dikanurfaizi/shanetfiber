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
        //$customers = 
        return [
            'password' => ['nullable', 'string', 'confirmed', 'min:8'],
            //'paket_id' => 'required',
            'ppp_type_id' => 'required'
        ];
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
            'password.required' => 'Password PPP is required',
            'paket_id.required' => 'Paket is required',

        ];
    }
}
