<x-layouts.cpanel.customers.app-layout :title="'Detail Customer'">

    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Step 5: Review & Confirm</h5>
        </div>

        <div class="card-body">

            {{-- SECTION COMPONENT (biar rapi & konsisten) --}}
            @php
                function section($title, $slot)
                {
                    return "
            <div class='mb-4 p-3 border rounded bg-light'>
                <h5 class='text-primary mb-3' style='font-weight: 600;'>$title</h5>
                $slot
            </div>";
                }
            @endphp

            {{-- ========== 1. CUSTOMER INFORMATION ========== --}}
            {!! section(
                '1. Customer Information',
                "
                                <table class='table table-sm table-bordered mb-0'>
                                    <tr><th width='30%'>PIC / Responsibility</th> <td>{$step1['user_name']}</td></tr>
                                    <tr><th>Name</th>         <td>{$step1['name']}</td></tr>
                                    <tr><th>Email</th>        <td>{$step1['email']}</td></tr>
                                    <tr><th>Contact</th>      <td>{$step1['contact']}</td></tr>
                                    <tr><th>Address</th>      <td>{$step1['address']}</td></tr>
                                </table>
                            ",
            ) !!}

            {{-- ========== 2. SERVER & INTERNET SERVICE ========== --}}
            @php
                $serviceRows =
                    $step2['internet_service'] === 'ppp'
                        ? "
                <tr><th>PPP Type</th><td>{$step2['ppp_type_name']}</td></tr>
              "
                        : "
                <tr><th>Interface</th><td>{$step2['interface']}</td></tr>
                <tr><th>IP Static</th><td>{$step2['ip_static']}</td></tr>
                <tr><th>MAC</th>      <td>{$step2['mac_address']}</td></tr>
              ";
            @endphp

            {!! section(
                '2. Server & Internet Service',
                "
                                <table class='table table-sm table-bordered mb-0'>
                                    <tr><th width='30%'>Server</th> <td>{$step2['mikrotik_name']}</td></tr>
                                    <tr><th>Service Type</th>      <td>" .
                    strtoupper($step2['internet_service']) .
                    "</td></tr>
                                    $serviceRows
                                </table>
                            ",
            ) !!}

            {{-- ========== 3. PACKAGE & MEMBERSHIP ========== --}}
            {!! section(
                '3. Package, Duration & Membership',
                "
                                <table class='table table-sm table-bordered mb-0'>
                                    <tr><th width='30%'>Package</th>  <td>{$step3['package_name']}</td></tr>
                                    <tr><th>Duration</th>             <td>{$step3['duration_label']}</td></tr>
                                    <tr><th>Member</th>               <td>{$step3['member_label']}</td></tr>
                                    <tr>
                                        <th>Discount</th>
                                        <td>Rp " .
                    number_format($step3['discount_nominal']) .
                    " ({$step3['discount_percent']}%)</td>
                                    </tr>
                                </table>
                            ",
            ) !!}

            {{-- ========== 4. ADDONS ========== --}}
            @php
                $addonsHtml = empty($addons)
                    ? "<p class='text-muted mb-0'>Tidak ada addons.</p>"
                    : "<table class='table table-sm table-bordered mb-0'>
                    <thead class='table-light'>
                        <tr>
                            <th>Addon</th>
                            <th width='10%'>Qty</th>
                            <th width='15%'>Unit Price</th>
                            <th width='15%'>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>";
            @endphp

            @if (!empty($addons))
                @foreach ($addons as $a)
                    @php $addon = $addonItems->firstWhere('id', $a['id']); @endphp
                    @php
                        $addonsHtml .=
                            "
                    <tr>
                        <td>{$addon->name}</td>
                        <td>{$a['qty']}</td>
                        <td>Rp " .
                            number_format($a['unit_price']) .
                            "</td>
                        <td>Rp " .
                            number_format($a['qty'] * $a['unit_price']) .
                            "</td>
                    </tr>";
                    @endphp
                @endforeach
                @php $addonsHtml .= "</tbody></table>"; @endphp
            @endif

            {!! section('4. Addons', $addonsHtml) !!}

        </div>

        <div class="card-footer text-end">
            <a href="{{ route('wizard.step4') }}" class="btn btn-secondary">← Back</a>

            <form class="d-inline" method="POST" action="{{ route('wizard.process') }}">
                @csrf
                <button class="btn btn-success">
                    <i class="fas fa-check"></i> Process & Create Customer
                </button>
            </form>
        </div>
    </div>

</x-layouts.cpanel.customers.app-layout>
