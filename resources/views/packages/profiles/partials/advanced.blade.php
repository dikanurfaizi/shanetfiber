<div class="row mb-1 col-md-12">
    <label class="text-primary"><strong>{{ __('Advanced Options') }}</strong></label>
</div>

<div class="mb-3 row">
    <div class="col-sm-3">
        <label for="bridge_learning"><strong>{{ __('Bridge Learning') }}</strong></label>
        {!! Form::select(
            'bridge_learning',
            ['default' => 'default', 'yes' => 'yes', 'no' => 'no'],
            $pppProfile->bridge_learning ?? 'default',
            [
                'class' => 'form-control',
            ],
        ) !!}
    </div>
    <div class="col-sm-3">
        <label for="use_ipv6"><strong>{{ __('Use IPv6') }}</strong></label>
        {!! Form::select(
            'use_ipv6',
            ['default' => 'default', 'yes' => 'yes', 'no' => 'no'],
            $pppProfile->use_ipv6 ?? 'yes',
            [
                'class' => 'form-control',
            ],
        ) !!}
    </div>
    <div class="col-sm-3">
        <label for="use_mpls"><strong>{{ __('Use MPLS') }}</strong></label>
        {!! Form::select(
            'use_mpls',
            ['default' => 'default', 'yes' => 'yes', 'no' => 'no'],
            $pppProfile->use_mpls ?? 'default',
            [
                'class' => 'form-control',
            ],
        ) !!}
    </div>
    <div class="col-sm-3">
        <label for="use_compression"><strong>{{ __('Use Compression') }}</strong></label>
        {!! Form::select(
            'use_compression',
            ['default' => 'default', 'yes' => 'yes', 'no' => 'no'],
            $pppProfile->use_compression ?? 'default',
            [
                'class' => 'form-control',
            ],
        ) !!}
    </div>
</div>

<div class="mb-3 row">
    <div class="col-sm-3">
        <label for="use_encryption"><strong>{{ __('Use Encryption') }}</strong></label>
        {!! Form::select(
            'use_encryption',
            ['default' => 'default', 'yes' => 'yes', 'no' => 'no'],
            $pppProfile->use_encryption ?? 'yes',
            [
                'class' => 'form-control',
            ],
        ) !!}
    </div>
    <div class="col-sm-3">
        <label for="only_one"><strong>{{ __('Only One') }}</strong></label>
        {!! Form::select(
            'only_one',
            ['default' => 'default', 'yes' => 'yes', 'no' => 'no'],
            $pppProfile->only_one ?? 'yes',
            [
                'class' => 'form-control',
            ],
        ) !!}
    </div>
    <div class="col-sm-3">
        <label for="change_tcp_mss"><strong>{{ __('Change TCP MSS') }}</strong></label>
        {!! Form::select(
            'change_tcp_mss',
            ['default' => 'default', 'yes' => 'yes', 'no' => 'no'],
            $pppProfile->change_tcp_mss ?? 'default',
            [
                'class' => 'form-control',
            ],
        ) !!}
    </div>
    <div class="col-sm-3">
        <label for="use_upnp"><strong>{{ __('Use UPNP') }}</strong></label>
        {!! Form::select(
            'use_upnp',
            ['default' => 'default', 'yes' => 'yes', 'no' => 'no'],
            $pppProfile->use_upnp ?? 'default',
            [
                'class' => 'form-control',
            ],
        ) !!}
    </div>
</div>
