<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Customers\Customer;

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
            'package_id' => 'required',
            'contact' => 'digits_between:10,15|nullable',
            'address' => ['nullable', 'string'],
            'member_id' => ['nullable', 'exists:members,id'],
            'user_id' => ['required', 'exists:users,id'],
            'billing_option' => ['nullable', 'in:1,2'],
            'periode_billing' => ['nullable', 'string'],
            'activation_date' => ['nullable', 'date'],
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
