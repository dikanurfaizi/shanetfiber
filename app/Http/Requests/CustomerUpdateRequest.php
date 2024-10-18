<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Customer;

class CustomerUpdateRequest extends FormRequest
{
    public function rules()
    {
        $customer = $this->route('customer');
        return [
            'name' => ['required', 'string'],
            'password' => ['nullable', 'string', 'confirmed', 'min:8'],
            'discount' => ['numeric'],
            'email' => ['nullable', 'email', 'string', 'max:255', Rule::unique('customers')->ignore($customer->slug, 'slug')],
            'paket_id' => 'required',
            'contact' => 'digits_between:10,15|nullable',
        ];
    }

    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->password == null) {
            $this->request->remove('password');
        }
    }
}
