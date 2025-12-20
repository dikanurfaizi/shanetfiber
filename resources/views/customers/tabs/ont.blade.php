@include('customers.partials.table-open')

@if ($ont)

    <tr>
        <td><strong>OLT</strong></td>
        <td>: {{ $ont->olt_name ?? '-' }}</td>

        <td><strong>ONT Index</strong></td>
        <td>: {{ $ont->ont_index ?? '-' }}</td>
    </tr>

    <tr>
        <td><strong>PON Group</strong></td>
        <td>: {{ $ont->pon_group ?? '-' }}</td>

        <td><strong>ONT Name</strong></td>
        <td>: {{ $ont->name ?? '-' }}</td>
    </tr>

    <tr>
        <td><strong>MAC Address</strong></td>
        <td>: {{ $ont->mac_address ?? '-' }}</td>

        <td><strong>Status</strong></td>
        <td>:
            @if ($ont->status === 'Up')
                <span class="badge bg-success text-white">Online</span>
            @elseif ($ont->status === 'Down')
                <span class="badge bg-danger text-white">Offline</span>
            @else
                <span class="badge bg-secondary text-white">Unknown</span>
            @endif
        </td>
    </tr>

    <tr>
        <td><strong>RX Power</strong></td>
        <td>: {{ $ont->rx_power ?? '-' }} dBm</td>

        <td><strong>TX Power</strong></td>
        <td>: {{ $ont->tx_power ?? '-' }} dBm</td>
    </tr>

    <tr>
        <td><strong>Temperature</strong></td>
        <td>: {{ $ont->temperature ?? '-' }}</td>

        <td><strong>Last Updated</strong></td>
        <td>: {{ $ont->last_updated ?? '-' }}</td>
    </tr>
@else
    <tr>
        <td colspan="4" class="text-danger text-center">
            No ONT matched for this customer (Based on MAC Address).
        </td>
    </tr>
@endif

@include('customers.partials.table-close')
