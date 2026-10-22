<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;

class GetMikrotikProfilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mikrotik_id' => 'required|integer|exists:mikrotiks,id',
        ];
    }

    public function messages(): array
    {
        return [
            'mikrotik_id.required' => 'Mikrotik ID is required.',
            'mikrotik_id.exists'   => 'Selected Mikrotik server does not exist.',
        ];
    }
}
