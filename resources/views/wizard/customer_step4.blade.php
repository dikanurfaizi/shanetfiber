<x-layouts.cpanel.customers.app-layout :title="'Detail Customer'">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold">{{ __('customer.wizard.step.4') }}</h5>
        </div>

        <form method="POST" action="{{ route('wizard.step4') }}">
            @csrf

            <div class="card-body">
                <p class="text-muted">{{ __('customer.wizard.message.addons_instruction') }}</p>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th width="60" class="text-center">{{ __('customer.wizard.label.select_addon') }}
                                </th>
                                <th>{{ __('customer.wizard.label.addon_name') }}</th>
                                <th>{{ __('customer.wizard.label.price') }}</th>
                                <th width="140px">{{ __('customer.wizard.label.qty') }}</th>
                                <th>{{ __('customer.wizard.label.subtotal') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($addons as $addon)
                                @php
                                    $oldChecked = old("addons.{$addon->id}.id") ? true : false;
                                    $oldQty = old("addons.{$addon->id}.qty", 1);
                                    $unitPrice = $addon->price;
                                    $initialSubtotal = $oldChecked ? $oldQty * $unitPrice : 0;
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input addon-check"
                                            data-addon="{{ $addon->id }}" name="addons[{{ $addon->id }}][id]"
                                            value="{{ $addon->id }}" @checked($oldChecked)>
                                    </td>

                                    <td>
                                        <span class="fw-semibold">{{ $addon->name }}</span>
                                    </td>

                                    <td>
                                        <span>Rp {{ number_format($unitPrice, 0, ',', '.') }}</span>
                                    </td>

                                    <td>
                                        <input type="number" min="1" class="form-control qty-input"
                                            data-addon="{{ $addon->id }}" name="addons[{{ $addon->id }}][qty]"
                                            value="{{ $oldQty }}" @disabled(!$oldChecked)>
                                    </td>

                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">Rp</span>
                                            <input type="text" class="form-control subtotal-input bg-light"
                                                data-addon="{{ $addon->id }}"
                                                name="addons[{{ $addon->id }}][subtotal]"
                                                value="{{ number_format($initialSubtotal, 0, ',', '.') }}" readonly>
                                        </div>
                                    </td>

                                    <input type="hidden" name="addons[{{ $addon->id }}][unit_price]"
                                        value="{{ $unitPrice }}">
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <a href="{{ route('wizard.step3') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-1"></i> {{ __('customer.wizard.button.back') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ __('customer.wizard.button.next_step') }} <i class="fa fa-arrow-right ms-1"></i>
                </button>
            </div>

        </form>
    </div>

    @push('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {

                function updateSubtotal(addonId) {
                    const checkbox = document.querySelector(`input.addon-check[data-addon="${addonId}"]`);
                    const qtyInput = document.querySelector(`input.qty-input[data-addon="${addonId}"]`);
                    const subtotalField = document.querySelector(`input.subtotal-input[data-addon="${addonId}"]`);
                    const unitPriceInput = document.querySelector(`input[name="addons[${addonId}][unit_price]"]`);

                    if (!checkbox || !qtyInput || !subtotalField || !unitPriceInput) return;

                    const unitPrice = parseFloat(unitPriceInput.value) || 0;

                    if (!checkbox.checked) {
                        qtyInput.disabled = true;
                        subtotalField.value = "0";
                        return;
                    }

                    qtyInput.disabled = false;
                    const qty = parseInt(qtyInput.value) || 1;
                    if (qty < 1) qtyInput.value = 1;

                    const subtotal = (qtyInput.disabled ? 0 : (parseInt(qtyInput.value || 1))) * unitPrice;

                    subtotalField.value = subtotal.toLocaleString('id-ID');
                }

                // Inisialisasi awal saat load halaman (untuk penanganan old input/re-render error)
                document.querySelectorAll('.addon-check').forEach(chk => {
                    updateSubtotal(chk.dataset.addon);

                    chk.addEventListener('change', function() {
                        updateSubtotal(this.dataset.addon);
                    });
                });

                document.querySelectorAll('.qty-input').forEach(qty => {
                    qty.addEventListener('input', function() {
                        updateSubtotal(this.dataset.addon);
                    });
                });

            });
        </script>
    @endpush

</x-layouts.cpanel.customers.app-layout>
