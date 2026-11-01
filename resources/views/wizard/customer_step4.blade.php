<x-layouts.cpanel.customers.app-layout :title="'Detail Customer'">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">{{ __('customer.wizard.step.4') }}</h5>
        </div>

        <form method="POST" action="{{ route('wizard.step4') }}">
            @csrf

            <div class="card-body">
                <p>{{ __('customer.wizard.message.addons_instruction') }}</p>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('customer.wizard.label.select_addon') }}</th>
                            <th>{{ __('customer.wizard.label.addon_name') }}</th>
                            <th>{{ __('customer.wizard.label.price') }}</th>
                            <th width="140px">{{ __('customer.wizard.label.qty') }}</th>
                            <th>{{ __('customer.wizard.label.subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($addons as $addon)
                            <tr>
                                <td>
                                    <input type="checkbox" class="addon-check" data-addon="{{ $addon->id }}"
                                        name="addons[{{ $addon->id }}][id]" value="{{ $addon->id }}">
                                </td>

                                <td>{{ $addon->name }}</td>

                                <td>Rp {{ number_format($addon->price) }}</td>

                                <td>
                                    <input type="number" min="1" class="form-control qty-input"
                                        data-addon="{{ $addon->id }}" name="addons[{{ $addon->id }}][qty]"
                                        value="1" disabled>
                                </td>

                                <td>
                                    <input type="text" class="form-control subtotal-input"
                                        data-addon="{{ $addon->id }}" name="addons[{{ $addon->id }}][subtotal]"
                                        value="0" readonly>
                                </td>

                                <input type="hidden" name="addons[{{ $addon->id }}][unit_price]"
                                    value="{{ $addon->price }}">
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('wizard.step3') }}"
                    class="btn btn-secondary">{{ __('customer.wizard.button.back') }}</a>
                <button class="btn btn-primary">{{ __('customer.wizard.button.next_step') }}</button>
            </div>

        </form>
    </div>

    @section('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {

                function updateSubtotal(addonId) {
                    const checkbox = document.querySelector(`input.addon-check[data-addon="${addonId}"]`);
                    const qtyInput = document.querySelector(`input.qty-input[data-addon="${addonId}"]`);
                    const subtotalField = document.querySelector(`input.subtotal-input[data-addon="${addonId}"]`);
                    const unitPrice = parseInt(
                        document.querySelector(`input[name="addons[${addonId}][unit_price]"]`).value
                    );

                    if (!checkbox.checked) {
                        qtyInput.disabled = true;
                        subtotalField.value = "0";
                        return;
                    }

                    qtyInput.disabled = false;
                    const qty = parseInt(qtyInput.value || 1);
                    const subtotal = qty * unitPrice;

                    subtotalField.value = subtotal.toLocaleString('id-ID');
                }

                document.querySelectorAll('.addon-check').forEach(chk => {
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
    @endsection

</x-layouts.cpanel.customers.app-layout>
