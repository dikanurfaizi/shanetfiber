<x-layouts.cpanel.customers.app-layout title="Edit Member">


    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            {{ $member->type . ' Member - Rp ' . number_format($member->discount, 2, ',', '.') }}
        </h6>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($member, [
                'method' => 'PATCH',
                'route' => ['members.update', $member->slug],
                'id' => 'member-form',
            ]) !!}
            <div class="card-body col-lg-8">
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="type"><strong>{{ __('member.label.name') }}</strong></label>
                        {!! Form::text('type', null, ['placeholder' => __('member.placeholder.name'), 'class' => 'form-control']) !!}
                        @if ($errors->has('type'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center mb-3">
                    <div class="col-md-12 mb-3">
                        <label for="discount"><strong>{{ __('member.label.discount') }}</strong></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    {!! Form::text('discount', null, [
                                        'placeholder' => __('member.placeholder.discount'),
                                        'class' => 'form-control',
                                        'id' => 'discount',
                                        'oninput' => 'formatNumber(this)',
                                        'onload' => 'formatNumber(this)',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        @if ($errors->has('discount'))
                            <span class="text-danger">{{ $errors->first('discount') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="status"><strong>{{ __('member.label.status') }}</strong></label>
                        <div class="d-flex justify-content-between">
                            <div class="form-switch">
                                <input type="hidden" name="status" value="0">
                                <input class="form-check-input" type="checkbox" name="status" id="status"
                                    value="1" {{ old('status', $member->status ?? '') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label"
                                    for="status-form">{{ old('status', $member->status ?? '') == '1' ? 'Active' : 'Inactive' }}</label>
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
            {!! Form::close() !!}
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
    @endsection
</x-layouts.cpanel.customers.app-layout>

<script>
    function formatNumber(input) {
        let value = input.value.replace(/[^0-9]/g, ''); // Hapus semua karakter kecuali angka
        value = Number(value).toLocaleString('id-ID'); // Format ke pemisah ribuan
        input.value = value;
    }

    // Bersihin pemisah ribuan sebelum submit form
    const form = document.getElementById('member-form'); // Ganti dengan ID form kamu
    form.addEventListener('submit', function() {
        const discountInput = document.getElementById('discount');
        discountInput.value = discountInput.value.replace(/\./g, ''); // Hapus titik pemisah ribuan
    });

    // Panggil formatNumber pas onload buat input yang udah ada valuenya
    window.addEventListener('load', function() {
        const discountInput = document.getElementById('discount');
        if (discountInput) {
            formatNumber(discountInput);
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const statusCheckbox = document.getElementById('status');
        const statusLabel = document.querySelector('label[for="status-form"]');

        function updateStatusLabel() {
            statusLabel.textContent = statusCheckbox.checked ? 'Active' : 'Inactive';
        }

        // Update label saat halaman dimuat
        updateStatusLabel();

        // Update label saat checkbox diubah
        statusCheckbox.addEventListener('change', updateStatusLabel);
    });
</script>
