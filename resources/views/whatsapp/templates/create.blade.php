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
                    <div class="mb-3 has-feedback row mb-2">
                        <div class="col-md-3">
                            <label for="name" class="col-form-label">{!! trans('whatsapp.message.label.name') !!}</label>
                        </div>
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" name="name" id="name" value="{{ old('name') }}"
                                    placeholder="{{ __('whatsapp.number.ph.name') }}"
                                    class="form-control @error('name') is-invalid @enderror">
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Keyword --}}
                    <div class="mb-3 has-feedback row mb-2" id="input-keyword">
                        <div class="col-md-3">
                            <label for="incoming_message" class="col-form-label">{!! trans('whatsapp.message.label.keyword') !!}</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="incoming_message" id="incoming_message"
                                value="{{ old('incoming_message') }}"
                                placeholder="{{ __('whatsapp.message.ph.incoming_message') }}"
                                class="form-control @error('incoming_message') is-invalid @enderror">
                            @error('incoming_message')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Reply Message --}}
                    <div class="mb-3 has-feedback row mb-2">
                        <div class="col-md-3">
                            <label for="reply_message" class="col-form-label">{!! trans('whatsapp.message.label.reply_message') !!}</label>
                        </div>
                        <div class="col-md-8">
                            <textarea name="reply_message" id="reply_message" placeholder="{{ __('whatsapp.message.ph.reply_message') }}"
                                class="form-control @error('reply_message') is-invalid @enderror" rows="4">{{ old('reply_message') }}</textarea>
                            @error('reply_message')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="mb-3 has-feedback row mb-2">
                        <div class="col-md-3">
                            <label for="description" class="col-form-label">{!! trans('whatsapp.message.label.description') !!}</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="description" id="description" value="{{ old('description') }}"
                                placeholder="{{ __('whatsapp.number.ph.description') }}"
                                class="form-control @error('description') is-invalid @enderror">
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa fa-save"></i> &nbsp {{ __('whatsapp.message.button.create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('custom_scripts')
    @endsection
</x-layouts.cpanel.settings.app-layout>
