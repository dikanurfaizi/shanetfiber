<x-layouts.cpanel.customers.app-layout :title="'Detail Customer'">

    {{-- CDN Leaflet CSS untuk Render Preview Peta --}}
    @if (!empty($step1['latitude']) && !empty($step1['longitude']))
        @push('custom_styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        @endpush
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-info text-white py-3">
            <h5 class="mb-0 fw-bold">{{ __('customer.wizard.step.5') }}</h5>
        </div>

        <div class="card-body">

            @php
                // Variabel penampung total biaya seluruh addon
                $totalAddons = 0;

                // LOGIKA RENDER MAP PREVIEW UNTUK STEP 1 REVIEW
                $mapPreviewRow = '';
                if (!empty($step1['latitude']) && !empty($step1['longitude'])) {
                    $mapPreviewRow =
                        '
                    <tr>
                        <th>' .
                        __('customer.wizard.label.coordinates') .
                        '</th>
                        <td>' .
                        $step1['latitude'] .
                        ', ' .
                        $step1['longitude'] .
                        '</td>
                    </tr>
                    <tr>
                        <th>' .
                        __('customer.wizard.label.location_map') .
                        '</th>
                        <td>
                            <div id="review-map" style="height: 200px; width: 100%; border-radius: 6px; border: 1px solid #ced4da; z-index: 1;"></div>
                        </td>
                    </tr>';
                }
            @endphp

            {{-- ========== 1. CUSTOMER INFORMATION ========== --}}
            <div class="mb-4 p-3 border rounded bg-light">
                <h5 class="text-primary mb-3 fw-semibold">{{ __('customer.wizard.step.customer_profile') }}</h5>
                <table class="table table-sm table-bordered mb-0 align-middle">
                    <tr>
                        <th width="30%">{{ __('customer.wizard.label.pic') }}</th>
                        <td>{{ $step1['user_name'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('customer.wizard.label.name') }}</th>
                        <td>{{ $step1['name'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('customer.wizard.label.email') }}</th>
                        <td>{{ $step1['email'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('customer.wizard.label.contact') }}</th>
                        <td>{{ $step1['contact'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('customer.wizard.label.address') }}</th>
                        <td>{{ $step1['address'] ?? '-' }}</td>
                    </tr>
                    {!! $mapPreviewRow !!}
                </table>
            </div>

            {{-- ========== 2. SERVER & INTERNET SERVICE ========== --}}
            @php
                $serviceRows =
                    ($step2['internet_service'] ?? '') === 'ppp'
                        ? '<tr><th>' .
                            __('customer.wizard.label.ppp_type') .
                            '</th><td>' .
                            ($step2['ppp_type_name'] ?? '-') .
                            '</td></tr>'
                        : '<tr><th>' .
                            __('customer.wizard.label.interface') .
                            '</th><td>' .
                            ($step2['interface'] ?? '-') .
                            '</td></tr>
                       <tr><th>' .
                            __('customer.wizard.label.ip_static') .
                            '</th><td>' .
                            ($step2['ip_static'] ?? '-') .
                            '</td></tr>
                       <tr><th>' .
                            __('customer.wizard.label.mac_address') .
                            '</th><td>' .
                            ($step2['mac_address'] ?? '-') .
                            '</td></tr>';
            @endphp

            <div class="mb-4 p-3 border rounded bg-light">
                <h5 class="text-primary mb-3 fw-semibold">{{ __('customer.wizard.step.server_configuration') }}</h5>
                <table class="table table-sm table-bordered mb-0 align-middle">
                    <tr>
                        <th width="30%">{{ __('customer.wizard.label.server') }}</th>
                        <td>{{ $step2['mikrotik_name'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('customer.wizard.label.service_type') }}</th>
                        <td>{{ strtoupper($step2['internet_service'] ?? '-') }}</td>
                    </tr>
                    {!! $serviceRows !!}
                </table>
            </div>

            {{-- ========== 3. PACKAGE & MEMBERSHIP ========== --}}
            @php
                $prorateCalculationRows = '';
                $catalogPrice = $step3['package_price'] ?? 0;

                if (isset($step3['is_prorate']) && $step3['is_prorate'] == '1' && ($step3['current_day'] ?? 0) > 15) {
                    $exactProrateRaw = $catalogPrice * ($step3['active_days'] / $step3['total_days']);

                    $prorateCalculationRows =
                        '
                    <tr>
                        <th>' .
                        __('customer.wizard.label.prorate_formula') .
                        '</th>
                        <td>
                            (' .
                        $step3['active_days'] .
                        ' / ' .
                        $step3['total_days'] .
                        ' ' .
                        __('customer.wizard.label.day') .
                        ')
x Rp ' .
                        number_format($catalogPrice, 0, ',', '.') .
                        ' = 
                            <span class="text-muted">Rp ' .
                        number_format($exactProrateRaw, 2, ',', '.') .
                        '</span>
                        </td>
                    </tr>
                    <tr>
                        <th>' .
                        __('customer.wizard.label.prorate_price_rounded') .
                        '</th>
                        <td><strong>Rp ' .
                        number_format($step3['price_before_discount'] ?? 0, 0, ',', '.') .
                        '</strong> <small class="text-muted">(Kelipatan Rp 500 ke atas)</small></td>
                    </tr>';
                } else {
                    $prorateCalculationRows =
                        '
                    <tr>
                        <th>' .
                        __('customer.wizard.label.package_price') .
                        '</th>
                        <td>Rp ' .
                        number_format($catalogPrice, 0, ',', '.') .
                        '</td>
                    </tr>';
                }
            @endphp

            <div class="mb-4 p-3 border rounded bg-light">
                <h5 class="text-primary mb-3 fw-semibold">{{ __('customer.wizard.step.package_and_membership') }}</h5>
                <table class="table table-sm table-bordered mb-0 align-middle">
                    <tr>
                        <th width="35%">{{ __('customer.wizard.label.package') }}</th>
                        <td>{{ $step3['package_name'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('customer.wizard.label.basic_price') }}</th>
                        <td>Rp {{ number_format($catalogPrice, 0, ',', '.') }} /
                            {{ __('customer.wizard.label.month') }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('customer.wizard.label.duration') }}</th>
                        <td>{{ $step3['duration_label'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('customer.wizard.label.calculation') }}</th>
                        <td><span
                                class="badge bg-light text-dark border">{{ $step3['calculation_type_label'] ?? '-' }}</span>
                        </td>
                    </tr>

                    {!! $prorateCalculationRows !!}

                    <tr>
                        <th>{{ __('customer.wizard.label.membership') }}</th>
                        <td>{{ $step3['member_label'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('customer.wizard.label.discount') }}</th>
                        <td class="text-danger">- Rp {{ number_format($step3['discount_nominal'] ?? 0, 0, ',', '.') }}
                            ({{ $step3['discount_percent'] ?? 0 }}%)</td>
                    </tr>
                    <tr class="table-success fw-bold">
                        <th>{{ __('customer.wizard.label.grand_total_billing') }}</th>
                        <td>
                            <span class="fs-6 text-success">Rp
                                {{ number_format($step3['final_bill_price'] ?? 0, 0, ',', '.') }}</span>
                            <br>
                            <small class="text-muted fw-normal">
                                (Rp {{ number_format($step3['price_before_discount'] ?? $catalogPrice, 0, ',', '.') }}
                                - Rp {{ number_format($step3['discount_nominal'] ?? 0, 0, ',', '.') }})
                            </small>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- ========== 4. ADDONS ========== --}}
            <div class="mb-4 p-3 border rounded bg-light">
                <h5 class="text-primary mb-3 fw-semibold">{{ __('customer.wizard.step.addons') }}</h5>
                @if (empty($addons))
                    <p class="text-muted mb-0">{{ __('customer.wizard.message.no_addons') }}</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0 align-middle table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('customer.wizard.label.addon_name') }}</th>
                                    <th width="10%" class="text-center">{{ __('customer.wizard.label.qty') }}</th>
                                    <th width="20%">{{ __('customer.wizard.label.price') }}</th>
                                    <th width="20%">{{ __('customer.wizard.label.subtotal') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($addons as $a)
                                    @php
                                        $addon = isset($addonItems) ? $addonItems->firstWhere('id', $a['id']) : null;
                                        $subtotalItem = ($a['qty'] ?? 1) * ($a['unit_price'] ?? 0);
                                        $totalAddons += $subtotalItem;
                                    @endphp
                                    <tr>
                                        <td>{{ $addon->name ?? 'Addon #' . $a['id'] }}</td>
                                        <td class="text-center">{{ $a['qty'] ?? 1 }}</td>
                                        <td>Rp {{ number_format($a['unit_price'] ?? 0, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($subtotalItem, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- ========== 5. GRAND TOTAL (RINGKASAN AKHIR) ========== --}}
            @php
                $grandTotalValue = ($step3['final_bill_price'] ?? 0) + $totalAddons;
            @endphp

            <div class="d-flex justify-content-between align-items-center p-3 rounded shadow-sm bg-light border">
                <span class="h6 mb-0 text-dark fw-bold">{{ __('customer.wizard.label.grand_total') }}</span>
                <span class="h5 mb-0 text-success fw-bold">Rp {{ number_format($grandTotalValue, 0, ',', '.') }}</span>
            </div>

        </div>

        <div class="card-footer d-flex justify-content-between align-items-center">
            <a href="{{ route('wizard.step4') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left me-1"></i> {{ __('customer.wizard.button.back') }}
            </a>

            <form class="d-inline" method="POST" action="{{ route('wizard.process') }}">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check me-1"></i> {{ __('customer.wizard.button.process_customer') }}
                </button>
            </form>
        </div>
    </div>

    {{-- SCRIPT LEAFLET JS UNTUK INITIALIZE PREVIEW MAP --}}
    @if (!empty($step1['latitude']) && !empty($step1['longitude']))
        @push('custom_scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const lat = parseFloat("{{ $step1['latitude'] }}");
                    const lng = parseFloat("{{ $step1['longitude'] }}");

                    const map = L.map('review-map', {
                        dragging: false,
                        touchZoom: false,
                        scrollWheelZoom: false,
                        doubleClickZoom: false,
                        boxZoom: false,
                        keyboard: false,
                        zoomControl: false
                    }).setView([lat, lng], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(map);

                    L.marker([lat, lng]).addTo(map)
                        .bindPopup('<b>{{ $step1['name'] }}</b><br>{{ $step1['address'] ?? '' }}')
                        .openPopup();
                });
            </script>
        @endpush
    @endif

</x-layouts.cpanel.customers.app-layout>
