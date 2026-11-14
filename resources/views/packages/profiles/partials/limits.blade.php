{{-- Section Title --}}
<div class="row mb-1">
    <div class="col-12">
        <label class="text-primary"><strong>{{ __('Limits') }}</strong></label>
    </div>
</div>

{{-- Max Limit --}}
<div class="row">
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="max_upload"><strong>{{ __('package.profile.label.max_upload') }}</strong></label>
        <input type="text" name="max_upload" id="max_upload" class="form-control" placeholder="Ex: 5M"
            value="{{ old('max_upload', $pppProfile->max_upload ?? '') }}">
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="max_download"><strong>{{ __('package.profile.label.max_download') }}</strong></label>
        <input type="text" name="max_download" id="max_download" class="form-control" placeholder="Ex: 10M"
            value="{{ old('max_download', $pppProfile->max_download ?? '') }}">
    </div>

    <div class="col-sm-6 col-md-3 mb-3">
        <label for="burst_upload"><strong>{{ __('package.profile.label.burst_upload') }}</strong></label>
        <input type="text" name="burst_upload" id="burst_upload" class="form-control" placeholder="Ex: 10M"
            value="{{ old('burst_upload', $pppProfile->burst_upload ?? '') }}">
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="burst_download"><strong>{{ __('package.profile.label.burst_download') }}</strong></label>
        <input type="text" name="burst_download" id="burst_download" class="form-control" placeholder="Ex: 20M"
            value="{{ old('burst_download', $pppProfile->burst_download ?? '') }}">
    </div>
</div>

{{-- Burst Threshold + Burst Time --}}
<div class="row">
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="threshold_upload"><strong>{{ __('package.profile.label.threshold_upload') }}</strong></label>
        <input type="text" name="threshold_upload" id="threshold_upload" class="form-control" placeholder="Ex: 8M"
            value="{{ old('threshold_upload', $pppProfile->burst_threshold_upload ?? '') }}">
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="threshold_download"><strong>{{ __('package.profile.label.threshold_download') }}</strong></label>
        <input type="text" name="threshold_download" id="threshold_download" class="form-control"
            placeholder="Ex: 15M" value="{{ old('threshold_download', $pppProfile->burst_threshold_download ?? '') }}">
    </div>

    <div class="col-sm-6 col-md-3 mb-3">
        <label for="burst_time_upload"><strong>{{ __('package.profile.label.burst_time_upload') }}</strong></label>
        <input type="text" name="burst_time_upload" id="burst_time_upload" class="form-control" placeholder="Ex: 8"
            inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
            value="{{ old('burst_time_upload', $pppProfile->burst_time_upload ?? '') }}">
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="burst_time_download"><strong>{{ __('package.profile.label.burst_time_download') }}</strong></label>
        <input type="text" name="burst_time_download" id="burst_time_download" class="form-control"
            placeholder="Ex: 8" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
            value="{{ old('burst_time_download', $pppProfile->burst_time_download ?? '') }}">
    </div>
</div>

{{-- Limit At --}}
<div class="row">
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="limit_at_upload"><strong>{{ __('package.profile.label.limit_at_upload') }}</strong></label>
        <input type="text" name="limit_at_upload" id="limit_at_upload" class="form-control" placeholder="Contoh: 2M"
            value="{{ old('limit_at_upload', $pppProfile->limit_at_upload ?? '') }}">
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <label for="limit_at_download"><strong>{{ __('package.profile.label.limit_at_download') }}</strong></label>
        <input type="text" name="limit_at_download" id="limit_at_download" class="form-control"
            placeholder="Contoh: 5M" value="{{ old('limit_at_download', $pppProfile->limit_at_download ?? '') }}">
    </div>

    <div class="col-sm-6 col-md-3 mb-3">
        <label for="priority"><strong>{{ __('package.profile.label.priority') }}</strong></label>
        <select name="priority" class="form-control" id="priority">
            @for ($i = 8; $i >= 1; $i--)
                <option value="{{ $i }}" @selected(old('priority', $pppProfile->priority ?? '') == $i)>
                    {{ $i }}
                </option>
            @endfor
        </select>
    </div>

    <div class="col-sm-6 col-md-3 mb-3">
        <label for="rasio_cir"><strong>{{ __('package.profile.label.cir') }}</strong></label>
        <select name="rasio_cir" class="form-control" id="rasio_cir">
            @for ($i = 1; $i <= 8; $i++)
                <option value="{{ $i }}" @selected(old('rasio_cir', $pppProfile->rasio_cir ?? '') == $i)>
                    1:{{ $i }}
                </option>
            @endfor
        </select>
    </div>
</div>

{{-- Catatan Format Input --}}
<div class="row mb-2">
    <div class="col-12">
        <label class="text-primary fw-semibold d-block mb-1">Catatan:</label>
        <ul class="text-muted small ps-3 mb-0">
            <li>
                Gunakan format seperti <code>5M</code> atau <code>512k</code>.
                Jika hanya angka, sistem akan menganggap satuan <strong>K</strong>.
            </li>
            <li>
                Format ini tidak berlaku untuk <strong>Burst Time Upload</strong> dan
                <strong>Burst Time Download</strong>, cukup isi angka saja (dalam detik).
            </li>
            <li>
                Nilai <strong>Upload</strong> &amp; <strong>Download</strong> harus diisi
                secara berpasangan.
                Jika hanya salah satu yang diisi, maka nilai tersebut tidak akan dihitung ke dalam
                <strong>rate-limit</strong>,
                namun tetap akan disimpan ke database sebagai referensi tambahan.
            </li>
            <li>
                Jika nilai <strong>Burst</strong>, <strong>Threshold</strong>, <strong>Burst Time</strong>, dan
                <strong>Limit At</strong>
                tidak lengkap berpasangan, maka yang dihitung dalam <strong>rate-limit</strong> hanya <strong>Max
                    Limit</strong> dan
                <strong>Min Upload/Download</strong>.
            </li>
        </ul>
    </div>
</div>
