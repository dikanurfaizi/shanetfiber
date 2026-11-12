<x-layouts.app-layout title="Create Addon">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">
            {{ __('addon.header.create') }}
        </h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::open(['route' => 'addons.store', 'method' => 'POST', 'id' => 'addon-form']) !!}

            <div class="card-body col-lg-8">

                {{-- NAME --}}
                <div class="mb-3">
                    <label><strong>{{ __('addon.label.name') }}</strong></label>
                    {!! Form::text('name', old('name'), [
                        'placeholder' => __('addon.placeholder.name'),
                        'class' => 'form-control',
                        'required',
                    ]) !!}
                    @error('name')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- PRICE --}}
                <div class="mb-3">
                    <label><strong>{{ __('addon.label.price') }}</strong></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        {!! Form::text('price', old('price'), [
                            'class' => 'form-control',
                            'id' => 'price',
                            'oninput' => 'formatNumber(this)',
                            'placeholder' => __('addon.placeholder.price'),
                            'required',
                        ]) !!}
                    </div>
                    @error('price')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- RECURRING --}}
                <div class="mb-3">
                    <label><strong>{{ __('addon.label.is_recurring') }}</strong></label>
                    {!! Form::select('is_recurring', [0 => __('No'), 1 => __('Yes')], old('is_recurring', 0), [
                        'class' => 'form-control',
                    ]) !!}
                    @error('is_recurring')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- STATUS --}}
                <div class="mb-3">
                    <label><strong>{{ __('addon.label.status') }}</strong></label>
                    <div class="form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" id="status" value="1"
                            {{ old('is_active', 1) == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="status" id="status-label">
                            {{ old('is_active', 1) ? 'Active' : 'Inactive' }}
                        </label>
                    </div>
                    @error('is_active')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            {{-- FOOTER --}}
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i>&nbsp;{{ __('addon.button.save') }}
                </button>
                <a class="btn btn-sm btn-secondary" href="{{ route('addons.index') }}">
                    <i class="fa fa-arrow-left"></i>&nbsp;{{ __('addon.button.back') }}
                </a>
            </div>

            {!! Form::close() !!}
        </div>
    </div>

    {{-- SCRIPTS --}}
    @section('custom_scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {

                const statusCheckbox = document.getElementById('status');
                const statusLabel = document.getElementById('status-label');
                const priceInput = document.getElementById('price');

                function updateStatusLabel() {
                    statusLabel.textContent = statusCheckbox.checked ? 'Active' : 'Inactive';
                }

                updateStatusLabel();
                statusCheckbox.addEventListener('change', updateStatusLabel);

                // CLEAN PRICE BEFORE SUBMIT
                document.getElementById('addon-form').addEventListener('submit', function() {
                    if (priceInput.value) {
                        priceInput.value = priceInput.value.replace(/\./g, '');
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
