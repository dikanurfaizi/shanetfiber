<?php

namespace App\Http\Requests;

use App\Models\Packages\Package;
use Illuminate\Foundation\Http\FormRequest;

class ServerStoreRequest extends FormRequest
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
        // return [

        //     'name' => ['required', 'string', 'min:5', 'max:25'],
        //     'host' => ['required', 'string', 'unique:mikrotiks,host'],
        //     'username' => ['required', 'string', 'min:4', 'max:25'],
        //     'password' => ['required', 'string', 'min:5', 'max:15'],
        //     'port' => ['required', 'numeric'],
        // ];
        return [
            'name' => ['required', 'string', 'min:5', 'max:25'],
            'host' => ['required', 'string'],
            'username' => ['required', 'string', 'min:4', 'max:25'],
            'password' => ['required', 'string', 'min:5', 'max:15'],
            'port' => [
                'required',
                'numeric',
                \Illuminate\Validation\Rule::unique('mikrotiks')->where(function ($query) {
                    return $query->where('host', $this->host);
                }),
            ],
        ];
    }

    public function messages()
    {
        return [

            'name.required' => 'Name Server is required',
            'name.min' => 'Name Server must be at least 5 characters',
            'name.max' => 'Name Server must not be greater than 25 characters',
            'host.required' => 'Host or ip address is required',
            'host.string' => 'Host or ip address must be a string',
            'username.required' => 'Username router is required',
            'username.min' => 'Username must be at least 4 characters',
            'username.max' => 'Username must not be greater than 25 characters',
            'username.string' => 'Username must be a string',
            'password.required' => 'Password router is required',
            'password.min' => 'Password must be at least 4 characters',
            'password.max' => 'Password must not be greater than 15 characters',
            'password.string' => 'Password must be a string',
            'port.required' => 'Port router is required, default is 8728',
            'port.numeric' => 'Port field must be a number',
            'port.unique' => 'This combination of host and port already exists.',
        ];
    }
}
