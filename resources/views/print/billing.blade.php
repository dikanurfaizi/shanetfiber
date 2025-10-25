<!DOCTYPE html>

<head>

    <title>ShaNet Fiber</title>

</head>

<body id="page-top">
    <h5>Tagihan :
        @if (isset($name))
        Nama:
        {{ $name }}
        @endif
        @if (isset($address))
        Alamat:
        {{ $address }}
        @endif
        @if (isset($status))
        Status:
        {{ $status }}
        @endif
        @if (isset($month))
        Bulan:
        {{ $month }}
        @endif
        @if (isset($year))
        Tahun:
        {{ $year }}
        @endif
    </h5>
    <table border="1" cellspacing="0" cellpadding="5">
        <tr>
            <th>{{ __('billing.table.no') }}</th>
            <th>{{ __('billing.table.customer-name') }}</th>
            @if (!isset($address))
            <th>{{ __('billing.table.address') }}</th>
            @endif
            <th>{{ __('billing.table.periode') }}</th>
            <th>{{ __('billing.table.duedate') }}</th>
            <th>{{ __('billing.table.bill') }}</th>
            <th>{{ __('billing.table.bill-state') }}</th>
            <th>{{ __('Teller') }}</th>
        </tr>
        @foreach ($dataPrint as $billing)
        <tr>
            <td>{{$loop->iteration}}</td>

            <td>{{$billing->customer_name ?? 'no name'}}</td>
            @if (!isset($address))
            <td>{{ $billing->customer->address ?? 'no address' }}</td>
            @endif
            <td>{{ Carbon\Carbon::parse($billing->billing_periode->name)->format('M Y') }}</td>
            <td>{{ Carbon\Carbon::parse($billing->due_date)->format('d M Y') }}</td>
            <td>
                <span class="badge bg-blue-soft text-danger">
                    @if ($billing->base_price === 0)
                    {{ __('Free') }}
                    @else
                    @moneyIDR($billing->base_price)
                    @endif
                </span>
            </td>
            <td>
                @if ($billing->status === 'LS')
                {{ __('Lunas') }}
                @elseif ($billing->status === 'PL')
                {{ __('Mundur') }} {{ Carbon\Carbon::parse($billing->pay_later)->format('d M Y') }}
                @else
                {{ __('Belum Lunas') }}
                @endif
            </td>
            <td>
                @if ($billing->status === 'LS')
                {{$billing->teller_name }}
                @else
                {{ __('') }}
                @endif
            </td>


        </tr>
        @endforeach
    </table>
</body>

</html>