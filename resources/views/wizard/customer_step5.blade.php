<x-layouts.cpanel.customers.app-layout :title="'Detail Customer'">

    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">{{ __('customer.wizard.step.5') }}</h5>
        </div>

        <div class="card-body">

            {{-- SECTION COMPONENT --}}
            @php
                if (!function_exists('renderReviewSection')) {
                    function renderReviewSection($titleKey, $slot)
                    {
                        $translatedTitle = __($titleKey);
                        return "
                        <div class='mb-4 p-3 border rounded bg-light'>
                            <h5 class='text-primary mb-3' style='font-weight: 600;'>$translatedTitle</h5>
                            $slot
                        </div>";
                    }
                }

                // Variabel penampung total biaya seluruh addon
                $totalAddons = 0;
            @endphp

            {{-- ========== 1. CUSTOMER INFORMATION ========== --}}
            {!! renderReviewSection(
                'customer.wizard.step.customer_profile',
                "
                                                                                                                                                                <table class='table table-sm table-bordered mb-0'>
                                                                                                                                                                    <tr><th width='30%'>" .
                    __('customer.wizard.label.pic') .
                    "</th> <td>{$step1['user_name']}</td></tr>
                                                                                                                                                                    <tr><th>" .
                    __('customer.wizard.label.name') .
                    "</th>         <td>{$step1['name']}</td></tr>
                                                                                                                                                                    <tr><th>" .
                    __('customer.wizard.label.email') .
                    "</th>        <td>{$step1['email']}</td></tr>
                                                                                                                                                                    <tr><th>" .
                    __('customer.wizard.label.contact') .
                    "</th>      <td>{$step1['contact']}</td></tr>
                                                                                                                                                                    <tr><th>" .
                    __('customer.wizard.label.address') .
                    "</th>      <td>{$step1['address']}</td></tr>
                                                                                                                                                                </table>
                                                                                                                                                                ",
            ) !!}

            {{-- ========== 2. SERVER & INTERNET SERVICE ========== --}}
            @php
                $serviceRows =
                    $step2['internet_service'] === 'ppp'
                        ? '<tr><th>' .
                            __('customer.wizard.label.ppp_type') .
                            "</th><td>{$step2['ppp_type_name']}</td></tr>"
                        : '<tr><th>' .
                            __('customer.wizard.label.interface') .
                            "</th><td>{$step2['interface']}</td></tr>
                           <tr><th>" .
                            __('customer.wizard.label.ip_static') .
                            "</th><td>{$step2['ip_static']}</td></tr>
                           <tr><th>" .
                            __('customer.wizard.label.mac_address') .
                            "</th><td>{$step2['mac_address']}</td></tr>";
            @endphp

            {!! renderReviewSection(
                'customer.wizard.step.server_configuration',
                "
                                                                                                                                                                <table class='table table-sm table-bordered mb-0'>
                                                                                                                                                                    <tr><th width='30%'>" .
                    __('customer.wizard.label.server') .
                    "</th> <td>{$step2['mikrotik_name']}</td></tr>
                                                                                                                                                                    <tr><th>" .
                    __('customer.wizard.label.service_type') .
                    '</th>      <td>' .
                    strtoupper($step2['internet_service']) .
                    "</td></tr>
                                                                                                                                                                    $serviceRows
                                                                                                                                                                </table>
                                                                                                                                                                ",
            ) !!}

            {{-- ========== 3. PACKAGE & MEMBERSHIP ========== --}}
            @php
                $prorateDetailRow = '';

                // Jika pendaftaran menggunakan metode prorate, tampilkan rincian hari & periodenya
                if ($step3['is_prorate'] == '1') {
                    $prorateDetailRow =
                        "
                    <tr>
                        <th>" .
                        __('customer.wizard.label.prorate_details') .
                        "</th>
                        <td>
                            " .
                        __('customer.wizard.label.date') .
                        ": <strong>{$step3['current_day']}</strong> " .
                        __('customer.wizard.label.until') .
                        " <strong>{$step3['total_days']} {$step3['current_month']}</strong> 
                            ({$step3['active_days']} " .
                        __('customer.wizard.label.active_day') .
                        ")
</td>
                    </tr>";
                }
            @endphp

            {!! renderReviewSection(
                'customer.wizard.step.package_and_membership',
                "
                                                                                                                <table class='table table-sm table-bordered mb-0'>
                                                                                                                    <tr><th width='30%'>" .
                    __('customer.wizard.label.package') .
                    "</th>  <td>{$step3['package_name']}</td></tr>
                                                                                                                    <tr><th>" .
                    __('customer.wizard.label.package_price') .
                    '</th>        <td>Rp ' .
                    number_format($step3['package_price']) .
                    "</td></tr>
                                                                                                                    <tr><th>" .
                    __('customer.wizard.label.duration') .
                    "</th>             <td>{$step3['duration_label']}</td></tr>
                                                                                                                    <tr><th>" .
                    __('customer.wizard.label.membership') .
                    "</th>           <td>{$step3['member_label']}</td></tr>
                                                                                                                    <tr>
                                                                                                                        <th>" .
                    __('customer.wizard.label.discount') .
                    "</th>
                                                                                                                        <td>Rp " .
                    number_format($step3['discount_nominal']) .
                    " ({$step3['discount_percent']}%)</td>
                                                                                                                    </tr>
                                                                                                                    <tr>
                                                                                                                        <th>" .
                    __('customer.wizard.label.calculation') .
                    "</th>
                                                                                                                        <td>{$step3['calculation_type_label']}</td>
                                                                                                                    </tr>
                                                                                                                    $prorateDetailRow
                                                                                                                    <tr class='table-success'>
                                                                                                                        <th>" .
                    __('customer.wizard.label.grand_total_billing') .
                    "</th>
                                                                                                                        <td><strong>Rp " .
                    number_format($step3['final_bill_price']) .
                    "</strong></td>
                                                                                                                    </tr>
                                                                                                                </table>
                                                                                                                ",
            ) !!}

            {{-- ========== 4. ADDONS ========== --}}
            @php
                $addonsHtml = empty($addons)
                    ? "<p class='text-muted mb-0'>" . __('customer.wizard.message.no_addons') . '</p>'
                    : "<table class='table table-sm table-bordered mb-0'>
                    <thead class='table-light'>
                        <tr>
                            <th>" .
                        __('customer.wizard.label.addon_name') .
                        "</th>
                            <th width='10%'>" .
                        __('customer.wizard.label.qty') .
                        "</th>
                            <th width='15%'>" .
                        __('customer.wizard.label.price') .
                        "</th>
                            <th width='15%'>" .
                        __('customer.wizard.label.subtotal') .
                        "</th>
                        </tr>
                    </thead>
                    <tbody>";
            @endphp

            @if (!empty($addons))
                @foreach ($addons as $a)
                    @php
                        $addon = $addonItems->firstWhere('id', $a['id']);
                        $subtotalItem = $a['qty'] * $a['unit_price'];

                        // Akumulasikan subtotal addon ke total addon keseluruhan
                        $totalAddons += $subtotalItem;

                        $addonsHtml .=
                            "
                        <tr>
                            <td>{$addon->name}</td>
                            <td>{$a['qty']}</td>
                            <td>Rp " .
                            number_format($a['unit_price']) .
                            "</td>
                            <td>Rp " .
                            number_format($subtotalItem) .
                            "</td>
                        </tr>";
                    @endphp
                @endforeach
                @php $addonsHtml .= "</tbody></table>"; @endphp
            @endif

            {!! renderReviewSection('customer.wizard.step.addons', $addonsHtml) !!}

            {{-- ========== 💡 5. GRAND TOTAL (RINGKASAN AKHIR) ========== --}}
            @php
                $grandTotalValue = $step3['final_bill_price'] + $totalAddons;
                $grandTotalHtml =
                    "
                <div class='d-flex justify-content-between align-items-center p-2 rounded' style='background-color: #e9ecef;'>
                    <span class='h6 mb-0 text-dark' style='font-weight: 600;'>" .
                    __('customer.wizard.label.grand_total') .
                    "</span>
                    <span class='h5 mb-0 text-success' style='font-weight: 700;'>Rp " .
                    number_format($grandTotalValue) .
                    "</span>
                </div>";
            @endphp

            {!! $grandTotalHtml !!}

        </div>

        <div class="card-footer text-end mt-3">
            <a href="{{ route('wizard.step4') }}" class="btn btn-secondary">{{ __('customer.wizard.button.back') }}</a>

            <form class="d-inline" method="POST" action="{{ route('wizard.process') }}">
                @csrf
                <button class="btn btn-success">
                    <i class="fas fa-check"></i> {{ __('customer.wizard.button.process_customer') }}
                </button>
            </form>
        </div>
    </div>

</x-layouts.cpanel.customers.app-layout>
