<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.message.page-title-edit') }}">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">
            {{ __('whatsapp.message.title.edit-message-name', ['name' => $data->name]) }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($data, ['method' => 'PATCH', 'route' => ['whatsappmessage.update', $data->slug]]) !!}
            <div class="card-body col-lg-8">

                <div class="mb-3 has-feedback row mb-2">
                    <div class="col-md-3">
                        <label for="name">{!! trans('whatsapp.message.label.name') !!}</label>
                    </div>
                    <div class="col-md-8">
                        <div class="input-group">
                            {!! Form::text('name', null, ['placeholder' => __('whatsapp.number.ph.name'), 'class' => 'form-control']) !!}
                        </div>
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif

                    </div>
                </div>
                {{-- @if (!$data->default)
                    <div class="mb-3 has-feedback row mb-2" id="input-default-number">
                        <div class="col-md-3">
                            <label for="default_number">{!! trans('whatsapp.message.label.gateway-number') !!}</label>
                        </div>
                        <div class="col-md-8">
                            <select id="default_number" name="default_number" class="form-control">
                                <option value="">{!! trans('whatsapp.message.ph.select-number') !!}</option>
                            </select>
                            <div id="error_default_number" class="invalid-feedback"></div>
                        </div>
                    </div>
                @endif --}}
                @if (!$data->default)
                    <div class="mb-3 has-feedback row mb-2" id="input-keyword">
                        <div class="col-md-3">
                            <label for="incoming_message">{!! trans('whatsapp.message.label.keyword') !!}</label>
                        </div>
                        <div class="col-md-8">
                            {!! Form::text('incoming_message', null, [
                                'placeholder' => __('whatsapp.message.ph.incoming_message'),
                                'class' => 'form-control',
                            ]) !!}
                            @if ($errors->has('incoming_message'))
                                <span class="text-danger">{{ $errors->first('incoming_message') }}</span>
                            @endif
                        </div>
                    </div>
                @endif
                <div class="mb-3 has-feedback row mb-2">
                    <div class="col-md-3">
                        <label for="reply_message">{!! trans('whatsapp.message.label.reply_message') !!}</label>
                    </div>
                    <div class="col-md-8">

                        {!! Form::textarea('reply_message', null, [
                            'placeholder' => __('whatsapp.message.ph.reply_message'),
                            'class' => 'form-control',
                        ]) !!}
                        @if ($errors->has('reply_message'))
                            <span class="text-danger">{{ $errors->first('reply_message') }}</span>
                        @endif
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
                        @if ($errors->has('description'))
                            <span class="text-danger">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>

            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-save"></i> &nbsp
                    {{ __('whatsapp.message.button.update-message') }}</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @section('custom_scripts')
    @endsection
</x-layouts.cpanel.settings.app-layout>
