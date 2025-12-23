<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRestoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'restore_intent'        => 'required|in:CONFIRM_RESTORE',
            'package_id'            => 'required|integer',
            'package_duration_id'   => 'required|integer',
            'billing_option'        => 'nullable|in:monthly,one_time',
            'billing_periode_name'  => 'nullable|string',
            'member_id'             => 'nullable|integer',
            'addons'                => 'nullable|array',
        ];
    }

    public function payload(): array
    {
        return $this->only([
            'package_id',
            'package_duration_id',
            'billing_option',
            'billing_periode_name',
            'member_id',
            'addons',
        ]);
    }
}
