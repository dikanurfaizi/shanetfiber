<x-layouts.cpanel.settings.app-layout title="{{ __('whatsapp.number.page-title-add') }}">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary"> {{ __('whatsapp.number.page-title-add') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('whatsappnumber.store') }}" method="POST">
                @csrf

                <div class="card-body col-lg-8">

                    {{-- Name --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="name"
                                class="form-label"><strong>{{ __('whatsapp.number.label.name') }}</strong></label>
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

                    {{-- Number --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="number"
                                class="form-label"><strong>{{ __('whatsapp.number.label.number') }}</strong></label>
                            <input type="text" name="number" id="number" value="{{ old('number') }}"
                                placeholder="{{ __('whatsapp.number.ph.number') }}"
                                class="form-control @error('number') is-invalid @enderror">
                            @error('number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="description"
                                class="form-label"><strong>{{ __('whatsapp.number.label.description') }}</strong></label>
                            <div class="input-group">
                                <input type="text" name="description" id="description"
                                    value="{{ old('description') }}"
                                    placeholder="{{ __('whatsapp.number.ph.description') }}"
                                    class="form-control @error('description') is-invalid @enderror">
                            </div>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fa fa-save"></i> &nbsp {{ __('whatsapp.number.button.add-new-number') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @section('custom_scripts')
    @endsection
</x-layouts.cpanel.settings.app-layout>
