<x-layouts.cpanel.settings.app-layout title="Midtrans">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary"> {{ __('websystem.midtran.header-edit') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($midtran, ['method' => 'PATCH', 'route' => ['paymentgateway.midtran.update', $midtran->slug]]) !!}
            <div class="card-body col-lg-8">

                <!-- Hidden field untuk disabled -->
                <input type="hidden" name="disabled" value="true">
                <!-- Hidden field untuk midtrans_is_production -->
                <input type="hidden" name="midtrans_is_production" value="true">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="midtrans_merchant_id"><strong>{{ __('websystem.midtran.merchat_id') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('midtrans_merchant_id', null, [
                                'placeholder' => __('websystem.midtran.placeholder.merchant_id'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        @if ($errors->has('midtrans_merchant_id'))
                            <span class="text-danger">{{ $errors->first('midtrans_merchant_id') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="midtrans_client_key"><strong>{{ __('websystem.midtran.client_key') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('midtrans_client_key', null, [
                                'placeholder' => __('websystem.midtran.placeholder.client_key'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        @if ($errors->has('midtrans_client_key'))
                            <span class="text-danger">{{ $errors->first('midtrans_client_key') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="midtrans_server_key"><strong>{{ __('websystem.midtran.server_key') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('midtrans_server_key', null, [
                                'placeholder' => __('websystem.midtran.placeholder.server_key'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        @if ($errors->has('midtrans_server_key'))
                            <span class="text-danger">{{ $errors->first('midtrans_server_key') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="midtrans_sandbox_client_key"><strong>{{ __('websystem.midtran.sandbox_client_key') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('midtrans_sandbox_client_key', null, [
                                'placeholder' => __('websystem.midtran.placeholder.sandbox_client_key'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        @if ($errors->has('midtrans_sandbox_client_key'))
                            <span class="text-danger">{{ $errors->first('midtrans_sandbox_client_key') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="midtrans_sandbox_server_key"><strong>{{ __('websystem.midtran.sandbox_server_key') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('midtrans_sandbox_server_key', null, [
                                'placeholder' => __('websystem.midtran.placeholder.sandbox_server_key'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        @if ($errors->has('midtrans_sandbox_server_key'))
                            <span class="text-danger">{{ $errors->first('midtrans_sandbox_server_key') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <div class="d-flex justify-content-between">
                            <div class="form-switch">
                                <input class="form-check-input" @if ($midtran->midtrans_is_production === 'true') checked @endif
                                    value="true" type="checkbox" id="midtrans_is_production"
                                    name="midtrans_is_production">
                                <label class="form-check-label" for="midtrans_is_production"
                                    data-production="{{ __('websystem.midtran.production-mode') }}"
                                    data-development="{{ __('websystem.midtran.development-mode') }}">
                                </label>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <div class="d-flex justify-content-between">
                            <div class="form-switch">
                                <!-- Checkbox Disabled -->
                                <input class="form-check-input" value="false" type="checkbox" id="disabled"
                                    name="disabled" @if ($midtran->disabled === 'false') checked @endif>
                                <label class="form-check-label" for="disabled"
                                    data-disable="{{ __('websystem.midtran.enable') }}"
                                    data-enable="{{ __('websystem.midtran.disable') }}">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> &nbsp
                        {{ __('router.button.save') }}</button>
                    <a class="btn btn-sm btn-secondary" href="{{ route('paymentgateway.index') }}"><i
                            class="fa fa-arrow-left"></i>&nbsp{{ __('server.button.cancel') }}</a>

                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</x-layouts.cpanel.settings.app-layout>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const checkboxes = [{
                checkbox: document.getElementById("midtrans_is_production"),
                label: document.querySelector("label[for='midtrans_is_production']"),
                hidden: document.querySelector("input[name='midtrans_is_production']"),
                labelTrue: "data-production",
                labelFalse: "data-development",
                hiddenValueTrue: "true",
                hiddenValueFalse: "false"
            },
            {
                checkbox: document.getElementById("disabled"),
                label: document.querySelector("label[for='disabled']"),
                hidden: document.querySelector("input[name='disabled']"),
                labelTrue: "data-disable",
                labelFalse: "data-enable",
                hiddenValueTrue: "false",
                hiddenValueFalse: "true"
            }
        ];

        function updateState({
            checkbox,
            label,
            hidden,
            labelTrue,
            labelFalse,
            hiddenValueTrue,
            hiddenValueFalse
        }) {
            if (label) {
                label.textContent = checkbox.checked ?
                    label.getAttribute(labelTrue) :
                    label.getAttribute(labelFalse);
            }

            if (hidden) {
                hidden.value = checkbox.checked ? hiddenValueTrue : hiddenValueFalse;
            }
        }

        checkboxes.forEach(item => {
            updateState(item); // Initial render
            item.checkbox.addEventListener("change", () => updateState(item));
        });
    });
</script>
