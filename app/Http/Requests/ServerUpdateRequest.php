<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class ServerUpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'min:5', 'max:25'],
            // 'host' => ['required', 'string'],
            'username' => ['required', 'string', 'min:4', 'max:25'],
            'password' => ['nullable', 'string', 'min:5', 'max:15'],
            'port' => ['required', 'numeric'],
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
