@if ($secretActive)
    <div class="card">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold text-primary">Traffic Monitoring</h5>
            <div class="text-muted">Interface: pppoe-{{ $customer->username }}</div>
        </div>

        <div class="card-body">
            <div id="traffic-chart" data-iface="pppoe-{{ $customer->username }}"
                data-url="{{ route('mikrotik.traffic_interface', $mikrotik) }}" style="height: 300px;">
            </div>
        </div>
    </div>

    @section('custom_scripts')
        <script src="{{ asset('js/traffic-monitor.js') }}"></script>
    @endsection
@else
    <div class="alert alert-warning text-center py-4">
        No active PPPoE session — traffic monitoring unavailable.
    </div>
@endif
