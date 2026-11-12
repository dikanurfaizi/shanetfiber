<x-layouts.cpanel.settings.app-layout title="Tripays">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary"> {{ __('websystem.tripay.header-edit') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($tripay, ['method' => 'PATCH', 'route' => ['paymentgateway.tripay.update', $tripay->slug]]) !!}
            <div class="card-body col-lg-8">

                <!-- Hidden field untuk disabled -->
                <input type="hidden" name="disabled" value="true">
                <!-- Hidden field untuk tripays_is_production -->
                <input type="hidden" name="tripays_is_production" value="true">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="tripays_merchant_id"><strong>{{ __('websystem.tripay.merchat_id') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('tripays_merchant_id', null, [
                                'placeholder' => __('websystem.tripay.placeholder.merchant_id'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        @if ($errors->has('tripays_merchant_id'))
                            <span class="text-danger">{{ $errors->first('tripays_merchant_id') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="tripays_api_key"><strong>{{ __('websystem.tripay.api_key') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('tripays_api_key', null, [
                                'placeholder' => __('websystem.tripay.placeholder.api_key'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        @if ($errors->has('tripays_api_key'))
                            <span class="text-danger">{{ $errors->first('tripays_api_key') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="tripays_private_key"><strong>{{ __('websystem.tripay.private_key') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('tripays_private_key', null, [
                                'placeholder' => __('websystem.tripay.placeholder.private_key'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        @if ($errors->has('tripays_private_key'))
                            <span class="text-danger">{{ $errors->first('tripays_private_key') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="tripays_sandbox_api_key"><strong>{{ __('websystem.tripay.sandbox_api_key') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('tripays_sandbox_api_key', null, [
                                'placeholder' => __('websystem.tripay.placeholder.sandbox_api_key'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        @if ($errors->has('tripays_sandbox_api_key'))
                            <span class="text-danger">{{ $errors->first('tripays_sandbox_api_key') }}</span>
                        @endif
                    </div>
                </div>


                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label
                            for="tripays_sandbox_private_key"><strong>{{ __('websystem.tripay.sandbox_private_key') }}</strong></label>
                        <div class="input-group">
                            {!! Form::text('tripays_sandbox_private_key', null, [
                                'placeholder' => __('websystem.tripay.placeholder.sandbox_private_key'),
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                        @if ($errors->has('tripays_sandbox_private_key'))
                            <span class="text-danger">{{ $errors->first('tripays_sandbox_private_key') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <div class="d-flex justify-content-between">
                            <div class="form-switch">
                                <input class="form-check-input" @if ($tripay->tripays_is_production === 'true') checked @endif
                                    value="true" type="checkbox" id="tripays_is_production"
                                    name="tripays_is_production">
                                <label class="form-check-label" for="tripays_is_production"
                                    data-production="{{ __('websystem.tripay.production-mode') }}"
                                    data-development="{{ __('websystem.tripay.development-mode') }}">
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
                                    name="disabled" @if ($tripay->disabled === 'false') checked @endif>
                                <label class="form-check-label" for="disabled"
                                    data-disable="{{ __('websystem.tripay.enable') }}"
                                    data-enable="{{ __('websystem.tripay.disable') }}">
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
                checkbox: document.getElementById("tripays_is_production"),
                label: document.querySelector("label[for='tripays_is_production']"),
                hidden: document.querySelector("input[name='tripays_is_production']"),
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
