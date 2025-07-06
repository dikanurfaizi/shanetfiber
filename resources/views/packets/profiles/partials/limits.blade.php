{{-- Section Title --}}
<div class="row mb-1">
    <div class="col-12">
        <label class="text-primary"><strong>{{ __('Limits') }}</strong></label>
    </div>
</div>


{{-- Max Limit --}}
<div class="row">
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="max_upload"><strong>{{ __('paket.profile.label.max_upload') }}</strong></label>
        {!! Form::text('max_upload', $pppProfile->max_upload ?? old('max_upload'), [
            'class' => 'form-control',
            'id' => 'max_upload',
            'placeholder' => 'Contoh: 5M',
        ]) !!}
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="max_download"><strong>{{ __('paket.profile.label.max_download') }}</strong></label>
        {!! Form::text('max_download', $pppProfile->max_download ?? old('max_download'), [
            'class' => 'form-control',
            'id' => 'max_download',
            'placeholder' => 'Contoh: 10M',
        ]) !!}
    </div>

    <div class="col-sm-6 col-md-3 mb-3">
        <label for="burst_upload"><strong>{{ __('paket.profile.label.burst_upload') }}</strong></label>
        {!! Form::text('burst_upload', $pppProfile->burst_upload ?? old('burst_upload'), [
            'class' => 'form-control',
            'id' => 'burst_upload',
            'placeholder' => 'Contoh: 10M',
        ]) !!}
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="burst_download"><strong>{{ __('paket.profile.label.burst_download') }}</strong></label>
        {!! Form::text('burst_download', $pppProfile->burst_download ?? old('burst_download'), [
            'class' => 'form-control',
            'id' => 'burst_download',
            'placeholder' => 'Contoh: 20M',
        ]) !!}
    </div>
</div>

{{-- Burst Threshold + Burst Time --}}
<div class="row">
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="threshold_upload"><strong>{{ __('paket.profile.label.threshold_upload') }}</strong></label>
        {!! Form::text('threshold_upload', $pppProfile->burst_threshold_upload ?? old('threshold_upload'), [
            'class' => 'form-control',
            'id' => 'threshold_upload',
            'placeholder' => 'Contoh: 8M',
        ]) !!}
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="threshold_download"><strong>{{ __('paket.profile.label.threshold_download') }}</strong></label>
        {!! Form::text('threshold_download', $pppProfile->burst_threshold_download ?? old('threshold_download'), [
            'class' => 'form-control',
            'id' => 'threshold_download',
            'placeholder' => 'Contoh: 15M',
        ]) !!}
    </div>

    <div class="col-sm-6 col-md-3 mb-3">
        <label for="burst_time_upload"><strong>{{ __('paket.profile.label.burst_time_upload') }}</strong></label>
        {!! Form::text('burst_time_upload', $pppProfile->burst_time_upload ?? old('burst_time_upload'), [
            'class' => 'form-control',
            'id' => 'burst_time_upload',
            'placeholder' => 'Contoh: 8',
            'inputmode' => 'numeric',
            'oninput' => "this.value = this.value.replace(/[^0-9]/g, '')",
        ]) !!}
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="burst_time_download"><strong>{{ __('paket.profile.label.burst_time_download') }}</strong></label>
        {!! Form::text('burst_time_download', $pppProfile->burst_time_download ?? old('burst_time_download'), [
            'class' => 'form-control',
            'id' => 'burst_time_download',
            'placeholder' => 'Contoh: 8',
            'inputmode' => 'numeric',
            'oninput' => "this.value = this.value.replace(/[^0-9]/g, '')",
        ]) !!}
    </div>
</div>

{{-- Limit At --}}
<div class="row">
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="limit_at_upload"><strong>{{ __('paket.profile.label.limit_at_upload') }}</strong></label>
        {!! Form::text('limit_at_upload', $pppProfile->limit_at_upload ?? old('limit_at_upload'), [
            'class' => 'form-control',
            'id' => 'limit_at_upload',
            'placeholder' => 'Contoh: 2M',
        ]) !!}
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="limit_at_download"><strong>{{ __('paket.profile.label.limit_at_download') }}</strong></label>
        {!! Form::text('limit_at_download', $pppProfile->limit_at_download ?? old('limit_at_download'), [
            'class' => 'form-control',
            'id' => 'limit_at_download',
            'placeholder' => 'Contoh: 5M',
        ]) !!}
    </div>

    <div class="col-sm-6 col-md-3 mb-3">
        <label for="priority"><strong>{{ __('paket.profile.label.priority') }}</strong></label>
        <select name="priority" class="form-control" id="priority">
            @for ($i = 8; $i >= 1; $i--)
                <option value="{{ $i }}"
                    {{ ($pppProfile->priority ?? old('priority')) == $i ? 'selected' : '' }}>
                    {{ $i }}
                </option>
            @endfor
        </select>
    </div>

    <div class="col-sm-6 col-md-3 mb-3">
        <label for="rasio_cir"><strong>{{ __('paket.profile.label.cir') }}</strong></label>
        <select name="rasio_cir" class="form-control" id="rasio_cir">
            @for ($i = 1; $i <= 8; $i++)
                <option value="{{ $i }}"
                    {{ ($pppProfile->rasio_cir ?? old('rasio_cir')) == $i ? 'selected' : '' }}>
                    1:{{ $i }}</option>
            @endfor
        </select>
    </div>
</div>

{{-- Optional note --}}
<div class="row mb-2">
    <div class="col-12">
        <small class="text-muted d-block mb-1">
            <strong>Format:</strong> Gunakan format seperti <code>5M</code> atau <code>512k</code>. Jika hanya angka,
            sistem akan menganggap satuan <strong>K</strong>.
        </small>
        <small class="text-muted d-block mb-1">
            <strong>Pengecualian:</strong> Format ini tidak berlaku untuk <strong>Burst Time Upload</strong> dan
            <strong>Burst Time Download</strong>.
        </small>
        <small class="text-muted d-block">
            <strong>Perhatian:</strong> Masukkan nilai <strong>Upload</strong> &amp; <strong>Download</strong> secara
            berpasangan. Bila hanya salah satu yang diisi, sistem akan mengabaikan pasangan tersebut dan hanya menyimpan
            <strong>Max Limit</strong>.
        </small>
    </div>
</div>
