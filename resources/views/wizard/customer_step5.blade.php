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
                $prorateCalculationRows = '';

                // Ambil harga asli katalog paket dari database/session
                $catalogPrice = $step3['package_price'];

                // Jika pendaftaran menggunakan metode prorate (DAN di atas tanggal 15), bongkar baris rumus detailnya
                if (isset($step3['is_prorate']) && $step3['is_prorate'] == '1' && ($step3['current_day'] ?? 0) > 15) {
                    // Hitung estimasi murni tanpa pembulatan untuk visualisasi text
                    $exactProrateRaw = $catalogPrice * ($step3['active_days'] / $step3['total_days']);

                    $prorateCalculationRows =
                        "
        <tr>
            <th>" .
                        __('customer.wizard.label.prorate_formula') .
                        "</th>
            <td>
                ({$step3['active_days']} / {$step3['total_days']} " .
                        __('customer.wizard.label.day') .
                        ")
x Rp " .
                        number_format($catalogPrice) .
                        " = 
                <span class='text-muted'>Rp " .
                        number_format($exactProrateRaw, 2, ',', '.') .
                        "</span>
            </td>
        </tr>
        <tr>
            <th>" .
                        __('customer.wizard.label.prorate_price_rounded') .
                        "</th>
            <td><strong>Rp " .
                        number_format($step3['price_before_discount']) .
                        "</strong> <small class='text-muted'>(Kelipatan Rp 500 ke atas)</small></td>
        </tr>";
                } else {
                    // Jika bukan prorate (Full Cost), tampilkan baris standar harga penuh sebulan
                    $prorateCalculationRows =
                        "
        <tr>
            <th>" .
                        __('customer.wizard.label.package_price') .
                        "</th>
            <td>Rp " .
                        number_format($catalogPrice) .
                        "</td>
        </tr>";
                }
            @endphp

            {!! renderReviewSection(
                'customer.wizard.step.package_and_membership',
                "
                            <table class='table table-sm table-bordered mb-0'>
                                <tr>
                                    <th width='35%'>" .
                    __('customer.wizard.label.package') .
                    "</th>  
                                    <td>{$step3['package_name']}</td>
                                </tr>
                                <tr>
                                    <th>" .
                    __('customer.wizard.label.basic_price') .
                    "</th>        
                                    <td>Rp " .
                    number_format($catalogPrice) .
                    ' / ' .
                    __('customer.wizard.label.month') .
                    "</td>
                                </tr>
                                <tr>
                                    <th>" .
                    __('customer.wizard.label.duration') .
                    "</th>             
                                    <td>{$step3['duration_label']}</td>
                                </tr>
                                <tr>
                                    <th>" .
                    __('customer.wizard.label.calculation') .
                    "</th>
                                    <td><span class='badge bg-light text-dark border'>" .
                    $step3['calculation_type_label'] .
                    "</span></td>
                                </tr>
                                
                                {{-- 💡 Suntik Rumus / Hasil Prorate di sini sebelum dipotong Member --}}
                                {$prorateCalculationRows}
                                
                                <tr>
                                    <th>" .
                    __('customer.wizard.label.membership') .
                    "</th>           
                                    <td>{$step3['member_label']}</td>
                                </tr>
                                <tr>
                                    <th>" .
                    __('customer.wizard.label.discount') .
                    "</th>
                                    <td class='text-danger'>- Rp " .
                    number_format($step3['discount_nominal']) .
                    " ({$step3['discount_percent']}%)</td>
                                </tr>
                                
                                <tr class='table-success fw-bold'>
                                    <th>" .
                    __('customer.wizard.label.grand_total_billing') .
                    "</th>
                                    <td>
                                        <span class='fs-6 text-success'>Rp " .
                    number_format($step3['final_bill_price']) .
                    "</span>
                                        <br>
                                        <small class='text-muted fw-normal'>
                                            (Rp " .
                    number_format($step3['price_before_discount'] ?? $catalogPrice) .
                    ' - Rp ' .
                    number_format($step3['discount_nominal']) .
                    ")
                                        </small>
                                    </td>
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
