<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.message.title.create') }}">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">
            {{ __('whatsapp.message.title.create') }}
        </h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('whatsappmessage.store') }}" method="POST">
                @csrf

                <div class="card-body col-lg-8">
                    {{-- Name --}}
                    <div class="row mb-3">
                        <label for="name" class="col-md-3 col-form-label">
                            {!! trans('whatsapp.message.label.name') !!}
                        </label>
                        <div class="col-md-8">
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                placeholder="{{ __('whatsapp.number.ph.name') }}"
                                class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- <div class="row mb-3" id="input-default-number">
                        <label for="default_number" class="col-md-3 col-form-label">
                            {!! trans('whatsapp.message.label.gateway-number') !!}
                        </label>
                        <div class="col-md-8">
                            <select id="default_number" name="default_number" class="form-select">
                                <option value="">{!! trans('whatsapp.message.ph.select-number') !!}</option>
                            </select>
                            <div id="error_default_number" class="invalid-feedback"></div>
                        </div>
                    </div> --}}

                    {{-- Keyword / Incoming Message --}}
                    <div class="row mb-3" id="input-keyword">
                        <label for="incoming_message" class="col-md-3 col-form-label">
                            {!! trans('whatsapp.message.label.keyword') !!}
                        </label>
                        <div class="col-md-8">
                            <input type="text" name="incoming_message" id="incoming_message"
                                value="{{ old('incoming_message') }}"
                                placeholder="{{ __('whatsapp.message.ph.incoming_message') }}"
                                class="form-control @error('incoming_message') is-invalid @enderror">
                            @error('incoming_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Reply Message --}}
                    <div class="row mb-3">
                        <label for="reply_message" class="col-md-3 col-form-label">
                            {!! trans('whatsapp.message.label.reply_message') !!}
                        </label>
                        <div class="col-md-8">
                            <textarea name="reply_message" id="reply_message" rows="4"
                                placeholder="{{ __('whatsapp.message.ph.reply_message') }}"
                                class="form-control @error('reply_message') is-invalid @enderror">{{ old('reply_message') }}</textarea>
                            @error('reply_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="row mb-3">
                        <label for="description" class="col-md-3 col-form-label">
                            {!! trans('whatsapp.message.label.description') !!}
                        </label>
                        <div class="col-md-8">
                            <input type="text" name="description" id="description" value="{{ old('description') }}"
                                placeholder="{{ __('whatsapp.number.ph.description') }}"
                                class="form-control @error('description') is-invalid @enderror">
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa fa-save"></i>&nbsp;{{ __('whatsapp.message.button.create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('custom_scripts')
    @endsection
</x-layouts.cpanel.settings.app-layout>
