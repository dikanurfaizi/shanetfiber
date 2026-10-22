<?php

namespace App\Http\Requests\Package;

use Illuminate\Foundation\Http\FormRequest;

class ImportPppProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profiles'                 => 'required|array|min:1',
            'profiles.*.profile_name'  => 'required|string',
            'profiles.*.max_upload'    => 'nullable|string',
            'profiles.*.max_download'  => 'nullable|string',
            'profiles.*.min_upload'    => 'nullable|string',
            'profiles.*.min_download'  => 'nullable|string',
            'profiles.*.rasio_cir'     => 'nullable|numeric',
            'profiles.*.priority'      => 'nullable|integer',
            'profiles.*.dns_server'    => 'nullable|string',
            'profiles.*.use_encryption' => 'nullable|string',
            'profiles.*.rate_limit'    => 'nullable|string',
            'profiles.*.only_one'      => 'nullable|string',
        ];
    }
}
