<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Customers\Customer;

class CustomerRouterUpdateRequest extends FormRequest
{
    public function rules()
    {
        //$customers = 
        return [
            'merk_ont' => 'required',
            //'type_ont' => 'required',
            'port' => 'numeric',
        ];
    }

    public function messages()
    {
        return [
            'merk_ont.required' => 'Please select router brand!',
            'type_ont.required' => 'Please select router type!',
            'port.numeric' => 'Thus port only numeric value.'

        ];
    }
}
