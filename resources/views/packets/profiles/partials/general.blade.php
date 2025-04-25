<div class="row mb-1">
    <div class="col-12">
        <label class="text-primary"><strong>{{ __('General') }}</strong></label>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <label for="profile_name"><strong>{{ __('paket.profile.label.profile_name') }}</strong></label>
        {!! Form::text('profile_name', null, [
            'placeholder' => __('paket.profile.placeholder.profile_name'),
            'class' => 'form-control',
        ]) !!}
        @error('profile_name')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="local_address"><strong>{{ __('paket.profile.label.local_address') }}</strong></label>
        {!! Form::text('local_address', null, [
            'placeholder' => __('paket.profile.placeholder.local_address'),
            'class' => 'form-control',
        ]) !!}
        @error('local_address')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="remote_address"><strong>{{ __('paket.profile.label.remote_address') }}</strong></label>
        {!! Form::text('remote_address', null, [
            'placeholder' => __('paket.profile.placeholder.remote_address'),
            'class' => 'form-control',
        ]) !!}
        @error('remote_address')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <label for="dns_server"><strong>{{ __('paket.profile.label.dns_server') }}</strong></label>
        {!! Form::text('dns_server', null, [
            'placeholder' => __('paket.profile.placeholder.dns_server'),
            'class' => 'form-control',
        ]) !!}
        @error('dns_server')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
</div>
