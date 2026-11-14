<x-layouts.app-layout title="Edit Membership">


    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-primary">
            {{ $member->name }}
        </h6>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('members.update', $member->slug) }}" method="POST" id="member-form">
                @csrf
                @method('PATCH')
            <div class="card-body col-lg-8">
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="type"><strong>{{ __('member.label.name') }}</strong></label>
                        <input type="text" name="name" class="form-control" placeholder="{{ __('member.placeholder.name') }}" value="{{ old('name', $member->name) }}">
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="discount_type"><strong>{{ __('member.label.type') }}</strong></label>
                        <select name="discount_type" id="discount_type" class="form-control">
                            @foreach (['none' => 'None', 'percent' => 'Percent (%)', 'amount' => 'Amount (Rp)'] as $val => $lbl)
                                <option value="{{ $val }}" {{ old('discount_type', $member->discount_type ?? 'none') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3" id="discount_value_wrapper">
                        <label><strong>{{ __('member.label.value') }}</strong></label>

                        <div id="discount_amount_group" class="d-none">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="discount_value" id="discount_amount" class="form-control" oninput="formatNumber(this)" value="{{ old('discount_value', $member->discount_value) }}">
                            </div>
                        </div>

                        <div id="discount_percent_group" class="d-none">
                            <div class="input-group">
                                <input type="number" name="discount_percent" id="discount_percent" class="form-control" step="0.01" min="0" max="100" value="{{ old('discount_percent', $member->discount_value) }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="status"><strong>{{ __('member.label.status') }}</strong></label>
                        <div class="d-flex justify-content-between">
                            <div class="form-switch">
                                <input type="hidden" name="status" value="0">
                                <input class="form-check-input" type="checkbox" name="status" id="status"
                                    value="1"
                                    {{ old('status', $member->is_active ?? '') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label"
                                    for="status-form">{{ old('status', $member->is_active ?? '') == '1' ? 'Active' : 'Inactive' }}</label>
                            </div>
                            @if ($errors->has('status'))
                                <span class="text-danger">{{ $errors->first('status') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success"><i
                        class="fas fa-user-check"></i>&nbsp{{ __('member.button.update') }}</button>
                <a class="btn btn-sm btn-secondary" href="{{ route('members.index') }}"><i
                        class="fa fa-arrow-left"></i>&nbsp{{ __('member.button.back') }}</a>
            </div>
            </form>
        </div>
    </div>
    @section('custom_styles')
        <link href="{{ asset('css/flatpickr.min.css') }}" rel="stylesheet">
        <style>
            #map {
                height: 100%;
            }
        </style>
    @endsection
    @section('custom_scripts')
        <script src="{{ asset('js/flatpickr') }}"></script>
        <script>
            config = {
                enableTime: false,
                dateFormat: 'd F Y',
                //format:'DD/MM/YYYY HH:mm:ss'
            }
            flatpickr("input[type=datetime-local]", config);
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const typeSelect = document.getElementById('discount_type');
                const wrapper = document.getElementById('discount_value_wrapper');
                const percentGroup = document.getElementById('discount_percent_group');
                const amountGroup = document.getElementById('discount_amount_group');

                const percentInput = document.getElementById('discount_percent');
                const amountInput = document.getElementById('discount_amount');

                const statusCheckbox = document.getElementById('status');
                const statusLabel = document.querySelector('label[for="status-form"]');

                function updateStatusLabel() {
                    statusLabel.textContent = statusCheckbox.checked ? 'Active' : 'Inactive';
                }

                // Update label saat halaman dimuat
                updateStatusLabel();

                // Update label saat checkbox diubah
                statusCheckbox.addEventListener('change', updateStatusLabel);

                function updateDiscountUI() {
                    const type = typeSelect.value;

                    // reset state
                    percentGroup.classList.add('d-none');
                    amountGroup.classList.add('d-none');
                    percentInput.disabled = true;
                    amountInput.disabled = true;

                    if (type === 'none') {
                        wrapper.classList.add('d-none');
                        percentInput.value = 0;
                        amountInput.value = 0;
                        percentInput.disabled = true;
                        amountInput.disabled = true;
                        return;
                    }

                    wrapper.classList.remove('d-none');

                    if (type === 'percent') {
                        percentGroup.classList.remove('d-none');
                        percentInput.disabled = false;
                    }

                    if (type === 'amount') {
                        amountGroup.classList.remove('d-none');
                        amountInput.disabled = false;
                    }
                }

                typeSelect.addEventListener('change', updateDiscountUI);
                updateDiscountUI();
            });

            document.getElementById('member-form').addEventListener('submit', function() {
                const amount = document.getElementById('discount_amount');
                if (amount && !amount.disabled) {
                    amount.value = amount.value.replace(/\./g, '');
                }
            });

            function formatNumber(input) {
                let value = input.value.replace(/[^0-9]/g, ''); // Hapus semua karakter kecuali angka
                value = Number(value).toLocaleString('id-ID'); // Format ke pemisah ribuan
                input.value = value;
            }
        </script>
    @endsection
</x-layouts.app-layout>
