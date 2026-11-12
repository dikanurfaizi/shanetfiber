<x-layouts.cpanel.settings.app-layout title="{{ __('telegram.gateway.header-edit') }}">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">
            {{ __('telegram.gateway.header-edit') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($data, ['method' => 'PATCH', 'route' => ['telegramgateway.update_description', $data->slug]]) !!}
            <div class="card-body col-lg-8">

                <div class="mb-3 has-feedback row mb-2" id="switch">
                    <div class="col-md-4">
                        <label for="gateway">{!! trans('telegram.gateway.page-title-index') !!}</label>
                    </div>
                    <div class="col-md-5">
                        <div class="form-switch">
                            <input class="form-check-input" value="false" type="checkbox" id="switch-gateway">
                            <input type="hidden" id="gateway" name="gateway" value="false">
                            <label class="form-check-label status-gateway" for="toggle-switch">
                                {{ trans('telegram.status.disable') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mb-3 has-feedback row mb-2">
                    <div class="col-md-4">
                        <label for="name">{!! trans('telegram.gateway.label.telegram-bot') !!}</label>
                    </div>
                    <div class="col-md-5">
                        {!! Form::text('name', null, [
                            'placeholder' => __('telegram.gateway.ph.telegram-bot'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>
                <div class="mb-3 has-feedback row mb-2">
                    <div class="col-md-4">
                        <label for="description">{!! trans('telegram.gateway.label.description') !!}</label>
                    </div>
                    <div class="col-md-5">
                        {!! Form::textarea('description', null, [
                            'placeholder' => __('telegram.gateway.ph.description'),
                            'class' => 'form-control',
                            'rows' => 5, // atur jumlah baris tinggi textarea
                        ]) !!}
                        @if ($errors->has('description'))
                            <span class="text-danger">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer">

                <a class="btn btn-sm btn-outline-danger" href="{{ route('telegramgateway.index') }}"><i
                        class="fa fa-arrow-left"></i>&nbsp{{ __('server.button.cancel') }}</a>
                <button type="submit" class="btn btn-sm btn-outline-success pull-right btn-save" id="confirm">
                    <span class="button-text"><i class="fa fa-save"></i>
                        &nbsp{{ __('telegram.gateway.button.update') }}</span>
                </button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @section('custom_scripts')
        @if ($data->disabled)
            <script>
                $('#switch #switch-gateway').prop("checked", false);
                $('#switch #gateway').val(true);
                $('#switch .form-check-label').text(
                    "{{ trans('telegram.status.disable') }}");
            </script>
        @else
            <script>
                $('#switch #switch-gateway').prop("checked", true);
                $('#switch #gateway').val(false);
                $('#switch .form-check-label').text(
                    "{{ trans('telegram.status.enable') }}");
            </script>
        @endif
        <script>
            $('#switch #switch-gateway').on('click', function(e) {
                if (e.currentTarget.checked) {
                    $('#switch #gateway').val(false);
                    $('#switch .form-check-label').text(
                        "{{ trans('telegram.status.enable') }}");
                } else {

                    $('#switch #gateway').val(true);
                    $('#switch .form-check-label').text(
                        "{{ trans('telegram.status.disable') }}");
                }
            });
        </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>
