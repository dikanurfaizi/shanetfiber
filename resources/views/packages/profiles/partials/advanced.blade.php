<div class="row mb-1 col-md-12">
    <label class="text-primary"><strong>{{ __('Advanced Options') }}</strong></label>
</div>

@php
    $pppOptions = ['default' => 'default', 'yes' => 'yes', 'no' => 'no'];
@endphp

<div class="mb-3 row">
    <div class="col-sm-3">
        <label for="bridge_learning"><strong>{{ __('Bridge Learning') }}</strong></label>
        <select name="bridge_learning" id="bridge_learning" class="form-control">
            @foreach ($pppOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('bridge_learning', $pppProfile->bridge_learning ?? 'default') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-3">
        <label for="use_ipv6"><strong>{{ __('Use IPv6') }}</strong></label>
        <select name="use_ipv6" id="use_ipv6" class="form-control">
            @foreach ($pppOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('use_ipv6', $pppProfile->use_ipv6 ?? 'yes') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-3">
        <label for="use_mpls"><strong>{{ __('Use MPLS') }}</strong></label>
        <select name="use_mpls" id="use_mpls" class="form-control">
            @foreach ($pppOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('use_mpls', $pppProfile->use_mpls ?? 'default') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-3">
        <label for="use_compression"><strong>{{ __('Use Compression') }}</strong></label>
        <select name="use_compression" id="use_compression" class="form-control">
            @foreach ($pppOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('use_compression', $pppProfile->use_compression ?? 'default') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="mb-3 row">
    <div class="col-sm-3">
        <label for="use_encryption"><strong>{{ __('Use Encryption') }}</strong></label>
        <select name="use_encryption" id="use_encryption" class="form-control">
            @foreach ($pppOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('use_encryption', $pppProfile->use_encryption ?? 'yes') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-3">
        <label for="only_one"><strong>{{ __('Only One') }}</strong></label>
        <select name="only_one" id="only_one" class="form-control">
            @foreach ($pppOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('only_one', $pppProfile->only_one ?? 'yes') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-3">
        <label for="change_tcp_mss"><strong>{{ __('Change TCP MSS') }}</strong></label>
        <select name="change_tcp_mss" id="change_tcp_mss" class="form-control">
            @foreach ($pppOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('change_tcp_mss', $pppProfile->change_tcp_mss ?? 'default') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-3">
        <label for="use_upnp"><strong>{{ __('Use UPNP') }}</strong></label>
        <select name="use_upnp" id="use_upnp" class="form-control">
            @foreach ($pppOptions as $value => $label)
                <option value="{{ $value }}" @selected(old('use_upnp', $pppProfile->use_upnp ?? 'default') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
