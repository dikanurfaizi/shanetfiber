<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.gateway.header-edit') }}">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">
            {{ __('whatsapp.gateway.header-edit') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($data, ['method' => 'PATCH', 'route' => ['whatsappgateway.update_gateway', $data->slug]]) !!}
            <div class="card-body col-lg-8">


                <div class="form-group has-feedback row mb-2">
                    <div class="col-md-4">
                        <label for="url_callback">{!! trans('whatsapp.gateway.label.url-callback') !!}</label>
                    </div>
                    <div class="col-md-8">

                        {!! Form::text('url_callback', null, [
                            'placeholder' => __('whatsapp.gateway.ph.url_callback'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('url_callback'))
                            <span class="text-danger">{{ $errors->first('url_callback') }}</span>
                        @endif

                    </div>
                </div>
                <div class="form-group has-feedback row mb-2">
                    <div class="col-md-4">
                        <label for="api_key">{!! trans('whatsapp.gateway.label.api-key') !!}</label>
                    </div>
                    <div class="col-md-8">

                        {!! Form::text('api_key', null, [
                            'placeholder' => __('whatsapp.gateway.ph.api_key'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('api_key'))
                            <span class="text-danger">{{ $errors->first('api_key') }}</span>
                        @endif

                    </div>
                </div>
                <div class="form-group has-feedback row mb-2">
                    <div class="col-md-4">
                        <label for="cert_file">{!! trans('whatsapp.gateway.label.cert-file') !!}</label>
                    </div>
                    <div class="col-md-8">
                        {!! Form::text('cert_file', null, [
                            'placeholder' => __('whatsapp.gateway.ph.cert_file'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('cert_file'))
                            <span class="text-danger">{{ $errors->first('cert_file') }}</span>
                        @endif

                    </div>
                </div>

            </div>
            <div class="card-footer">

                <a class="btn btn-sm btn-outline-danger" href="{{ route('whatsappgateway.index') }}"><i
                        class="fa fa-arrow-left"></i>&nbsp{{ __('server.button.cancel') }}</a>
                <button type="submit" class="btn btn-sm btn-outline-success pull-right btn-save" id="confirm">
                    <span class="button-text"><i class="fa fa-save"></i>
                        &nbsp{{ __('whatsapp.gateway.button.update') }}</span>
                </button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @section('custom_scripts')
    @endsection
</x-layouts.cpanel.settings.app-layout>
