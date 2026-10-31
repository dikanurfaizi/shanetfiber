<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServerUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $server = $this->route('server');

        return [
            'name'           => ['sometimes', 'required', 'string', 'min:3', 'max:50'],
            'host'           => ['sometimes', 'required', 'string'],
            'username'       => ['sometimes', 'required', 'string', 'min:3', 'max:50'],
            'password'       => ['nullable', 'string', 'min:3', 'max:50'],
            'port'           => [
                'sometimes',
                'required',
                'numeric',
                Rule::unique('mikrotiks')->where(function ($query) {
                    return $query->where('host', $this->host ?? $this->route('server')?->host);
                })->ignore($server?->id ?? $server),
            ],
            'radius_secret'  => ['nullable', 'string', 'max:255'],
            'coa_port'       => ['nullable', 'numeric'],
            'ros_version_id' => ['nullable', 'numeric'],
        ];
    }

    /**
     * Bersihkan input kosong agar tidak menimpa nilai database dengan null.
     */
    protected function prepareForValidation(): void
    {
        // Hapus field bertipe string kosong agar tidak ikut ter-update
        $nullableFields = ['password', 'radius_secret', 'coa_port', 'ros_version_id'];

        foreach ($nullableFields as $field) {
            if ($this->filled($field) === false || $this->input($field) === null || $this->input($field) === '') {
                $this->request->remove($field);
            }
        }
    }

    public function messages(): array
    {
        return [
            'name.required'     => __('validation.required', ['attribute' => __('server.label.name')]),
            'host.required'     => __('validation.required', ['attribute' => __('server.label.host')]),
            'username.required' => __('validation.required', ['attribute' => __('server.label.username')]),
            'port.required'     => __('validation.required', ['attribute' => __('server.label.port')]),
            'port.numeric'      => __('validation.numeric', ['attribute' => __('server.label.port')]),
            'port.unique'       => __('validation.unique', ['attribute' => __('server.label.port')]),
            'coa_port.numeric'  => __('validation.numeric', ['attribute' => __('server.label.coa_port')]),
        ];
    }
}
