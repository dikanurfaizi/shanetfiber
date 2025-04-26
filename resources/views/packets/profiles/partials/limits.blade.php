<div class="row mb-1">
    <div class="col-12">
        <label class="text-primary"><strong>{{ __('Limits') }}</strong></label>
    </div>
</div>

<div class="row">
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="max_upload"><strong>{{ __('paket.profile.label.max_limit') }}</strong></label>
        {!! Form::text('max_upload', isset($pppProfile) ? $pppProfile->max_upload : old('max_upload'), [
            'class' => 'form-control',
            'placeholder' => __('paket.profile.placeholder.max_upload'),
        ]) !!}
        @error('max_upload')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <label><strong>&nbsp;</strong></label>
        {!! Form::text('max_download', isset($pppProfile) ? $pppProfile->max_download : old('max_download'), [
            'class' => 'form-control',
            'placeholder' => __('paket.profile.placeholder.max_download'),
        ]) !!}
        @error('max_download')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="rasio_cir"><strong>{{ __('paket.profile.label.cir') }}</strong></label>
        <select name="rasio_cir" class="form-control">
            @for ($i = 1; $i <= 8; $i++)
                <option value="{{ $i }}"
                    {{ (isset($pppProfile) && $pppProfile->rasio_cir == $i) || old('rasio_cir') == $i ? 'selected' : '' }}>
                    1:{{ $i }}</option>
            @endfor
        </select>
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="priority"><strong>{{ __('paket.profile.label.priority') }}</strong></label>
        <select name="priority" class="form-control">
            @for ($i = 8; $i >= 1; $i--)
                <option value="{{ $i }}"
                    {{ (isset($pppProfile) && $pppProfile->priority == $i) || old('priority') == $i ? 'selected' : '' }}>
                    {{ $i }}</option>
            @endfor
        </select>
    </div>
</div>

<div class="row mb-2">
    <div class="col-12">
        <span class="text-secondary">{{ __('paket.profile.label.format_max_limit') }}</span>
    </div>
</div>
