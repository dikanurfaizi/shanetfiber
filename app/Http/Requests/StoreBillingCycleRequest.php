<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillingCycleRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:billing_cycles,code'],
            'name' => ['required', 'string', 'max:255'],
            'months' => ['required', 'integer', 'min:1', 'max:12'],
            'is_active' => ['boolean'],
        ];
    }
}
