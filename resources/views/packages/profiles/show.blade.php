<x-layouts.app-layout title="PPP Profile Show">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('PPP Profile Detail') }}</h5>
    </div>

    <div class="card-body col-lg-10">
        @php
            $fields = [
                'profile_name' => 'Profile Name',
                'local_address' => 'Local Address',
                'remote_address' => 'Remote Address',
                'dns_server' => 'DNS Server',
                'rate_limit' => 'Rate Limit',
                'rasio_cir' => 'Rasio CIR',
                'priority' => 'Priority',
                'parent_queue' => 'Parent Queue',
                'queue_type' => 'Queue Type',
                'bridge_learning' => 'Bridge Learning',
                'use_ipv6' => 'Use IPv6',
                'use_mpls' => 'Use MPLS',
                'use_compression' => 'Use Compression',
                'use_encryption' => 'Use Encryption',
                'only_one' => 'Only One',
                'change_tcp_mss' => 'Change TCP MSS',
                'use_upnp' => 'Use UPNP',
                'comment' => 'Comment',
                'script_on_up' => 'Script On Up',
                'script_on_down' => 'Script On Down',
                'insert_queue_before' => 'Insert Queue Before',
            ];
        @endphp

        <div class="row">
            @foreach ($fields as $key => $label)
                <div class="col-12 col-sm-6 col-md-3 mb-3"> <!-- Update grid to col-md-3 for 4 items per row -->
                    <div class="mb-3">
                        <strong>{{ $label }}:</strong> <br>
                        @if ($key == 'rasio_cir')
                            1:{{ $pppProfiles->$key ?? 'no data' }}
                        @else
                            {{ $pppProfiles->$key ?? 'no data' }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    <div class="card-footer">
        <a class="btn btn-sm btn-success" href="{{ route('ppp-profiles.edit', $pppProfiles) }}">
            <i class="fa fa-edit"></i> Edit
        </a>
        <a class="btn btn-sm btn-primary" href="{{ route('ppp-profiles.index') }}">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>

</x-layouts.app-layout>
