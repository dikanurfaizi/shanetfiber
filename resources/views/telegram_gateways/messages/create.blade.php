<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.message.title.create') }}">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">
            {{ __('whatsapp.message.title.create') }}
        </h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::open(['method' => 'POST', 'route' => 'whatsappmessage.store']) !!}
            <div class="card-body col-lg-8">

                <div class="mb-3 has-feedback row mb-2">
                    <div class="col-md-3">
                        <label for="name">{!! trans('whatsapp.message.label.name') !!}</label>
                    </div>
                    <div class="col-md-8">
                        <div class="input-group">
                            {!! Form::text('name', null, ['placeholder' => __('whatsapp.number.ph.name'), 'class' => 'form-control']) !!}
                        </div>
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- <div class="mb-3 has-feedback row mb-2" id="input-default-number">
                    <div class="col-md-3">
                        <label for="default_number">{!! trans('whatsapp.message.label.gateway-number') !!}</label>
                    </div>
                    <div class="col-md-8">
                        <select id="default_number" name="default_number" class="form-control">
                            <option value="">{!! trans('whatsapp.message.ph.select-number') !!}</option>
                        </select>
                        <div id="error_default_number" class="invalid-feedback"></div>
                    </div>
                </div> --}}

                <div class="mb-3 has-feedback row mb-2" id="input-keyword">
                    <div class="col-md-3">
                        <label for="incoming_message">{!! trans('whatsapp.message.label.keyword') !!}</label>
                    </div>
                    <div class="col-md-8">
                        {!! Form::text('incoming_message', null, [
                            'placeholder' => __('whatsapp.message.ph.incoming_message'),
                            'class' => 'form-control',
                        ]) !!}
                        @error('incoming_message')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mb-3 has-feedback row mb-2">
                    <div class="col-md-3">
                        <label for="reply_message">{!! trans('whatsapp.message.label.reply_message') !!}</label>
                    </div>
                    <div class="col-md-8">
                        {!! Form::textarea('reply_message', null, [
                            'placeholder' => __('whatsapp.message.ph.reply_message'),
                            'class' => 'form-control',
                        ]) !!}
                        @error('reply_message')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mb-3 has-feedback row mb-2">
                    <div class="col-md-3">
                        <label for="description">{!! trans('whatsapp.message.label.description') !!}</label>
                    </div>
                    <div class="col-md-8">
                        {!! Form::text('description', null, [
                            'placeholder' => __('whatsapp.number.ph.description'),
                            'class' => 'form-control',
                        ]) !!}
                        @error('description')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fa fa-save"></i> &nbsp {{ __('whatsapp.message.button.create') }}
                </button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @section('custom_scripts')
    @endsection
</x-layouts.cpanel.settings.app-layout>
