<x-layouts.app-layout title="Create Membership">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('member.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('members.store') }}" method="POST" id="member-form">
                @csrf

            <div class="card-body col-lg-8">

                {{-- NAME --}}
                <div class="mb-3">
                    <label><strong>{{ __('member.label.name') }}</strong></label>
                    <input type="text" name="name" class="form-control" placeholder="{{ __('member.placeholder.name') }}" value="{{ old('name') }}">
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- DISCOUNT TYPE --}}
                <div class="mb-3">
                    <label><strong>{{ __('member.label.type') }}</strong></label>
                    <select name="discount_type" id="discount_type" class="form-control">
                        @foreach (['none' => 'None', 'percent' => 'Percent (%)', 'amount' => 'Amount (Rp)'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('discount_type', 'none') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    @error('discount_type')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- DISCOUNT VALUE WRAPPER --}}
                <div class="mb-3 d-none" id="discount_value_wrapper">
                    <label><strong>{{ __('member.label.value') }}</strong></label>

                    {{-- AMOUNT --}}
                    <div id="discount_amount_group" class="d-none">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="discount_value" id="discount_amount" class="form-control" oninput="formatNumber(this)" value="{{ old('discount_value') }}">
                        </div>
                        @error('discount_value')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- PERCENT --}}
                    <div id="discount_percent_group" class="d-none">
                        <div class="input-group">
                            <input type="number" name="discount_percent" id="discount_percent" class="form-control" step="0.01" min="0" max="100" value="{{ old('discount_percent') }}">
                            <span class="input-group-text">%</span>
                        </div>
                        @error('discount_percent')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- STATUS --}}
                <div class="mb-3">
                    <label><strong>{{ __('member.label.status') }}</strong></label>
                    <div class="form-switch">
                        <input type="hidden" name="status" value="0">
                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1"
                            {{ old('status', 1) == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="status" id="status-label">
                            {{ old('status', 1) ? 'Active' : 'Inactive' }}
                        </label>
                    </div>
                    @error('status')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fas fa-user-plus"></i>&nbsp{{ __('member.button.save') }}
                </button>
                <a class="btn btn-sm btn-secondary" href="{{ route('members.index') }}">
                    <i class="fa fa-arrow-left"></i>&nbsp{{ __('member.button.back') }}
                </a>
            </div>

            </form>
        </div>
    </div>

    {{-- SCRIPTS --}}
    @section('custom_scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {

                const typeSelect = document.getElementById('discount_type');
                const wrapper = document.getElementById('discount_value_wrapper');
                const percentGroup = document.getElementById('discount_percent_group');
                const amountGroup = document.getElementById('discount_amount_group');

                const percentInput = document.getElementById('discount_percent');
                const amountInput = document.getElementById('discount_amount');

                const statusCheckbox = document.getElementById('status');
                const statusLabel = document.getElementById('status-label');

                // STATUS LABEL
                function updateStatusLabel() {
                    statusLabel.textContent = statusCheckbox.checked ? 'Active' : 'Inactive';
                }
                updateStatusLabel();
                statusCheckbox.addEventListener('change', updateStatusLabel);

                // DISCOUNT UI LOGIC
                function updateDiscountUI() {
                    const type = typeSelect.value;

                    // reset
                    percentGroup.classList.add('d-none');
                    amountGroup.classList.add('d-none');
                    percentInput.disabled = true;
                    amountInput.disabled = true;

                    if (type === 'none') {
                        wrapper.classList.add('d-none');
                        percentInput.value = 0;
                        amountInput.value = 0;
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
                updateDiscountUI(); // initial load

                // CLEAN FORMAT BEFORE SUBMIT
                document.getElementById('member-form').addEventListener('submit', function() {
                    if (!amountInput.disabled && amountInput.value) {
                        amountInput.value = amountInput.value.replace(/\./g, '');
                    }
                });

            });

            // Rupiah formatter
            function formatNumber(input) {
                let value = input.value.replace(/[^0-9]/g, '');
                value = Number(value).toLocaleString('id-ID');
                input.value = value;
            }
        </script>
    @endsection

</x-layouts.app-layout>
