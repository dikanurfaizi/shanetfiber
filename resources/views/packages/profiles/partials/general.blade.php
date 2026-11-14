<div class="row mb-1">
    <div class="col-12">
        <label class="text-primary"><strong>{{ __('General') }}</strong></label>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <label for="profile_name"><strong>{{ __('package.profile.label.profile_name') }}</strong></label>
        <input type="text" name="profile_name" id="profile_name" class="form-control"
            placeholder="{{ __('package.profile.placeholder.profile_name') }}"
            value="{{ old('profile_name', $pppProfile->profile_name ?? '') }}">
        @error('profile_name')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="local_address"><strong>{{ __('package.profile.label.local_address') }}</strong></label>
        <input type="text" name="local_address" id="local_address" class="form-control"
            placeholder="{{ __('package.profile.placeholder.local_address') }}"
            value="{{ old('local_address', $pppProfile->local_address ?? '') }}">
        @error('local_address')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="remote_address"><strong>{{ __('package.profile.label.remote_address') }}</strong></label>
        <input type="text" name="remote_address" id="remote_address" class="form-control"
            placeholder="{{ __('package.profile.placeholder.remote_address') }}"
            value="{{ old('remote_address', $pppProfile->remote_address ?? '') }}">
        @error('remote_address')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <label for="dns_server"><strong>{{ __('package.profile.label.dns_server') }}</strong></label>
        <input type="text" name="dns_server" id="dns_server" class="form-control"
            placeholder="{{ __('package.profile.placeholder.dns_server') }}"
            value="{{ old('dns_server', $pppProfile->dns_server ?? '') }}">
        @error('dns_server')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>
