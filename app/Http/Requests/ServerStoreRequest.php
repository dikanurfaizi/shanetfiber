<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        return [
            'name' => ['required', 'string', 'min:3', 'max:50'],
            'host' => ['required', 'string'],
            'username' => ['required', 'string', 'min:3', 'max:50'],
            'password' => ['required', 'string', 'min:3', 'max:50'],
            'port' => [
                'required',
                'numeric',
                Rule::unique('mikrotiks')->where(function ($query) {
                    return $query->where('host', $this->host);
                }),
            ],
            'radius_secret' => ['nullable', 'string', 'max:255'],
            'coa_port' => ['nullable', 'numeric'],
            'ros_version_id' => ['nullable', 'numeric'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('server.label.name')]),
            'name.min' => __('validation.min.string', ['attribute' => __('server.label.name'), 'min' => 3]),
            'name.max' => __('validation.max.string', ['attribute' => __('server.label.name'), 'max' => 50]),
            'host.required' => __('validation.required', ['attribute' => __('server.label.host')]),
            'username.required' => __('validation.required', ['attribute' => __('server.label.username')]),
            'password.required' => __('validation.required', ['attribute' => __('server.label.password')]),
            'port.required' => __('validation.required', ['attribute' => __('server.label.port')]),
            'port.numeric' => __('validation.numeric', ['attribute' => __('server.label.port')]),
            'port.unique' => __('validation.unique', ['attribute' => __('server.label.port')]),
            'radius_secret.string' => __('validation.string', ['attribute' => __('server.label.radius_secret')]),
            'coa_port.numeric' => __('validation.numeric', ['attribute' => __('server.label.coa_port')]),
        ];
    }
}
