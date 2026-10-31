<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServerTestRadiusRequest extends FormRequest
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
            'host'          => ['required', 'string'],
            'radius_secret' => ['required', 'string', 'max:255'],
            'coa_port'      => ['nullable', 'numeric'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'host.required'          => __('validation.required', ['attribute' => __('server.label.host')]),
            'radius_secret.required' => __('validation.required', ['attribute' => __('server.label.radius_secret')]),
            'coa_port.numeric'       => __('validation.numeric', ['attribute' => __('server.label.coa_port')]),
        ];
    }
}
