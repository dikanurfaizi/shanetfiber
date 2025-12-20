@if ($serverDisconnect === 'false')

    {{-- PPP MODE --}}
    @if ($customer->internet_service == 'ppp')
        @include('customers.tabs.server-ppp')

        {{-- IP STATIC MODE --}}
    @else
        @include('customers.tabs.server-static')
    @endif
@else
    @include('customers.tabs.server-offline')
@endif
