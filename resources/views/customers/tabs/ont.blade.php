<div class="ont-detail-table">
    @include('customers.partials.table-open')

    @if ($ont)

        <tr>
            <td><strong>OLT</strong></td>
            <td data-label="OLT">{{ $ont->olt_name ?? '-' }}</td>

            <td><strong>ONT Index</strong></td>
            <td data-label="ONT Index">{{ $ont->ont_index ?? '-' }}</td>
        </tr>

        <tr>
            <td><strong>PON Group</strong></td>
            <td data-label="PON Group">{{ $ont->pon_group ?? '-' }}</td>

            <td><strong>ONT Name</strong></td>
            <td data-label="ONT Name">{{ $ont->name ?? '-' }}</td>
        </tr>

        <tr>
            <td><strong>MAC Address</strong></td>
            <td data-label="MAC Address">{{ $ont->mac_address ?? '-' }}</td>

            <td><strong>Status</strong></td>
            <td data-label="Status">
                @if ($ont->status === 'Up')
                    <span class="badge bg-success text-white px-2 py-1">Online</span>
                @elseif ($ont->status === 'Down')
                    <span class="badge bg-danger text-white px-2 py-1">Offline</span>
                @else
                    <span class="badge bg-secondary text-white px-2 py-1">Unknown</span>
                @endif
            </td>
        </tr>

        <tr>
            <td><strong>RX Power</strong></td>
            <td data-label="RX Power">{{ $ont->rx_power ?? '-' }} dBm</td>

            <td><strong>TX Power</strong></td>
            <td data-label="TX Power">{{ $ont->tx_power ?? '-' }} dBm</td>
        </tr>

        <tr>
            <td><strong>Temperature</strong></td>
            <td data-label="Temperature">{{ $ont->temperature ?? '-' }}</td>

            <td><strong>Last Updated</strong></td>
            <td data-label="Last Updated">{{ $ont->last_updated ?? '-' }}</td>
        </tr>
    @else
        <tr>
            <td colspan="4" class="text-danger text-center">
                No ONT matched for this customer (Based on MAC Address).
            </td>
        </tr>
    @endif

    @include('customers.partials.table-close')
</div>

<style>
    /* ===============================
   ONT Detail Responsive
   =============================== */

    @media (max-width: 768px) {

        .ont-detail-table table,
        .ont-detail-table tbody,
        .ont-detail-table tr,
        .ont-detail-table td {
            display: block;
            width: 100%;
        }

        .ont-detail-table tr {
            margin-bottom: 1rem;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
        }

        .ont-detail-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.25rem 0;
            border: none;
        }

        .ont-detail-table td strong {
            display: none;
            /* label lama disembunyikan */
        }

        .ont-detail-table td[data-label]::before {
            content: attr(data-label);
            font-weight: 600;
            color: #6b7280;
            font-size: 0.85rem;
        }

        .badge {
            font-size: 0.75rem;
        }
    }
</style>
