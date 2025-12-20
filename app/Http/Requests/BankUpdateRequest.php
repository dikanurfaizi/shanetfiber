<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Customers\Customer;

class BankUpdateRequest extends FormRequest
{
    public function rules()
    {
        $bank = $this->route('bank');
        return [
            'name' => ['required', 'string', 'max:255'],
            'bank' => 'required',
            //'account_number' => 'required|unique:banks,name,' . $bank->id . ',id,bank,' . $bank->bank,
            //'name' => 'required|unique:packages,name,' . $packet->id . ',id,mikrotik_id,' . $packet->mikrotik_id
            'account_number' => ['required', 'string', 'max:255', Rule::unique('banks')->ignore($bank->slug, 'slug')],
        ];
    }

    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        //
    }
}
