<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerWizardStep4Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $raw = $this->addons ?? [];

        $filtered = collect($raw)
            ->filter(fn($item) => !empty($item['id']) && !empty($item['qty']))
            ->values()
            ->toArray();

        $this->merge(['addons' => $filtered]);
    }

    public function rules(): array
    {
        return [
            'addons'              => ['nullable', 'array'],
            'addons.*.id'         => ['required', 'exists:addons,id'],
            'addons.*.qty'        => ['required', 'integer', 'min:1'],
            'addons.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'addons.*.id.required'         => __('customer.wizard.validation.addon_id_required'),
            'addons.*.id.exists'           => __('customer.wizard.validation.addon_id_exists'),
            'addons.*.qty.required'        => __('customer.wizard.validation.addon_qty_required'),
            'addons.*.qty.integer'         => __('customer.wizard.validation.addon_qty_integer'),
            'addons.*.qty.min'             => __('customer.wizard.validation.addon_qty_min'),
            'addons.*.unit_price.required' => __('customer.wizard.validation.addon_unit_price_required'),
            'addons.*.unit_price.numeric'  => __('customer.wizard.validation.addon_unit_price_numeric'),
        ];
    }
}
