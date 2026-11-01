<x-layouts.cpanel.customers.app-layout :title="'Detail Customer'">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">{{ __('customer.wizard.step.3') }}</h5>
        </div>

        <form method="POST" action="{{ route('wizard.step3') }}">
            @csrf

            <div class="card-body">

                {{-- =======================
                SELECT PACKAGE
                ======================== --}}
                <div class="form-group mb-3">
                    <label><strong>{{ __('customer.wizard.label.package') }}</strong> <span
                            class="text-danger">*</span></label>
                    <select name="package_id" id="package-dropdown" class="form-control">
                        <option value="" data-price="0">{{ __('customer.wizard.placeholder.select_package') }}
                        </option>
                        @foreach ($packages as $pkg)
                            <option value="{{ $pkg->id }}" data-price="{{ $pkg->price }}">
                                {{ $pkg->name }} — Rp {{ number_format($pkg->price, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('package_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- =======================
                PACKAGE DURATION
                ======================== --}}
                <div class="form-group mb-3 d-none" id="duration-wrapper">
                    <label><strong>{{ __('customer.wizard.label.duration') }}</strong></label>
                    <select name="package_duration_id" id="duration-select" class="form-control">
                        <option value="">{{ __('customer.wizard.placeholder.select_duration') }}</option>
                    </select>
                    @error('package_duration_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- =======================
                MEMBERSHIP
                ======================== --}}
                <div class="form-group mb-3">
                    <label><strong>{{ __('customer.wizard.label.membership') }}</strong></label>
                    <select name="member_id" id="member-dropdown" class="form-control">
                        <option value="">{{ __('customer.wizard.placeholder.non_member') }}</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}">
                                {{ $member->name }} ({{ __('customer.wizard.label.discount') }}
                                {{ $member->discount_type === 'amount' ? 'Rp ' . number_format($member->discount_value) : (int) $member->discount_value . '%' }})
                            </option>
                        @endforeach
                    </select>
                    @error('member_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- =======================
                BILLING OPTION (💡 Ditambahkan id="billing-option-wrapper" & d-none)
                ======================== --}}
                <div class="form-group mb-3 d-none" id="billing-option-wrapper">
                    <label><strong>{{ __('customer.wizard.label.billing_option') }}</strong></label><br>

                    <div class="form-check form-check-inline">
                        <input type="radio" name="billing_option" value="monthly" class="form-check-input" checked>
                        <label class="form-check-label">{{ __('customer.wizard.label.monthly') }}</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input type="radio" name="billing_option" value="one_time" class="form-check-input">
                        <label class="form-check-label">{{ __('customer.wizard.label.one_time') }}</label>
                    </div>

                    @error('billing_option')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- =======================
                SELECT BILLING PERIODE (IF ONE TIME)
                ======================== --}}
                <div id="periode-wrapper" class="form-group mb-3 d-none">
                    <label><strong>{{ __('customer.wizard.label.select_billing_period') }}</strong></label>
                    <select name="billing_periode_name" id="periode-select" class="form-control">
                        <option value="">-- {{ __('customer.wizard.placeholder.select_billing_period') }} --
                        </option>
                        @foreach ($availablePeriodes as $periode)
                            <option value="{{ $periode['name'] }}">{{ $periode['name'] }}</option>
                        @endforeach
                    </select>
                    @error('billing_periode_name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- =======================
                DISCOUNT RESULT
                ======================== --}}
                <div class="form-group mb-3">
                    <label><strong>{{ __('customer.wizard.label.discount_applied') }}</strong></label>
                    <input type="text" id="discount_display" class="form-control" readonly>
                </div>

                {{-- =======================
                CALCULATION OPTION (💡 Ditambahkan d-none di awal akses halaman)
                ======================== --}}
                <div class="form-group mb-3 d-none" id="prorate-wrapper">
                    <label><strong>{{ __('customer.wizard.label.calculation') }}:</strong></label><br>

                    <div class="form-check form-check-inline">
                        <input type="radio" name="is_prorate" value="0" id="prorate-no" class="form-check-input"
                            checked>
                        <label class="form-check-label"
                            for="prorate-no">{{ __('customer.wizard.label.full_cost') }}</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input type="radio" name="is_prorate" value="1" id="prorate-yes"
                            class="form-check-input">
                        <label class="form-check-label"
                            for="prorate-yes">{{ __('customer.wizard.label.prorate') }}</label>
                    </div>

                    <div id="prorate-info-box" class="alert alert-secondary mt-2">
                        <small
                            id="prorate-summary-text">{{ __('customer.wizard.message.select_internet_package') }}</small>
                    </div>
                </div>

            </div>

            <div class="card-footer text-right">
                <a href="{{ route('wizard.step2') }}"
                    class="btn btn-secondary">{{ __('customer.wizard.button.back') }}</a>
                <button class="btn btn-primary">{{ __('customer.wizard.button.next_step') }}</button>
            </div>

        </form>
    </div>

    @section('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const packageDropdown = document.getElementById('package-dropdown');
                const durationWrapper = document.getElementById('duration-wrapper');
                const durationSelect = document.getElementById('duration-select');
                const memberDropdown = document.getElementById('member-dropdown');
                const discountDisplay = document.getElementById('discount_display');

                const billingOptionWrapper = document.getElementById('billing-option-wrapper'); // 💡 Element baru

                const prorateRadioYes = document.getElementById('prorate-yes');
                const prorateRadioNo = document.getElementById('prorate-no');
                const prorateWrapper = document.getElementById('prorate-wrapper'); // 💡 Wrapper Prorate keseluruhan
                const prorateInfoBox = document.getElementById('prorate-info-box');
                const prorateSummaryText = document.getElementById('prorate-summary-text');

                let currentPackagePrice = 0;
                let currentDiscountValue = 0;

                const today = new Date();
                const currentDay = today.getDate();

                // =======================================================================
                // 💡 PENGKONDISIAN AWAL TANGGAL (> 15) UNTUK PRORATE WRAPPER
                // =======================================================================
                if (currentDay > 15) {
                    prorateWrapper.classList.remove('d-none');
                } else {
                    prorateWrapper.classList.add('d-none');
                    // Pastikan value default yang terkirim jika disembunyikan adalah full cost (0)
                    prorateRadioNo.checked = true;
                }

                // =======================================================================
                // ENGINE KALKULATOR PRORATE UTAMA (API DRIVEN - 100% DRY & INFORMATIF)
                // =======================================================================
                function calculateClientProrate() {
                    const selectedOption = packageDropdown.options[packageDropdown.selectedIndex];
                    currentPackagePrice = selectedOption ? parseFloat(selectedOption.getAttribute('data-price')) : 0;

                    if (!packageDropdown.value || currentPackagePrice <= 0) {
                        prorateSummaryText.innerText = "{{ __('customer.wizard.message.select_internet_package') }}";
                        prorateInfoBox.className = "alert alert-secondary mt-2";
                        return;
                    }

                    const payload = {
                        package_price: currentPackagePrice,
                        discount_amount: currentDiscountValue,
                        is_prorate: prorateRadioYes.checked ? 1 : 0,
                        duration_months: 1
                    };

                    fetch("{{ route('wizard.calculate-prorate') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify(payload)
                        })
                        .then(response => response.json())
                        .then(res => {
                            if (!res.success) return;

                            let templateText = "";

                            // Pilihan A: Prorate disetujui oleh User DAN tanggal saat ini sudah melewati tanggal 15
                            if (prorateRadioYes.checked && res.current_day > 15) {
                                prorateInfoBox.className = "alert alert-success mt-2";

                                templateText = `
                <div class="d-flex flex-column gap-1 text-sm text-muted">
                    <div class="fw-bold text-success mb-1">✓ ${res.calculation_type_label} Aktif</div>
                    <div>• Harga paket asli: <b>Rp ${res.package_price_raw.toLocaleString()}</b></div>
                    <div>• Periode aktif berjalan: <b>${res.active_days} hari</b> dari total ${res.total_days} hari (${res.current_month})</div>
                    <div>• Hitung murni prorate: <span class="text-decoration-underline">(${res.active_days}/${res.total_days}) x Rp ${res.package_price_raw.toLocaleString()}</span> = <b>Rp ${res.exact_prorate_raw.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</b></div>
                    <div>• Pembulatan kelipatan Rp 500 ke atas: <b>Rp ${res.price_before_discount.toLocaleString()}</b></div>
                    ${res.discount_amount > 0 ? `<div class="text-danger">• Potongan Diskon Membership: <b>- Rp ${res.discount_amount.toLocaleString()}</b></div>` : ''}
                    <hr class="my-1 border-secondary-subtle">
                    <div class="text-dark fw-bold">Total Biaya Bulan Pertama: Rp ${res.price_before_discount.toLocaleString()} ${res.discount_amount > 0 ? `- Rp ${res.discount_amount.toLocaleString()}` : ''} = <span class="text-success fs-5">Rp ${res.price_final.toLocaleString()}</span></div>
                </div>
            `;
                            }
                            // Pilihan B: Menggunakan Full Cost Reguler (Tanpa Prorate)
                            else {
                                prorateInfoBox.className = "alert alert-info mt-2";

                                templateText = `
                <div class="d-flex flex-column gap-1 text-sm text-muted">
                    <div class="fw-bold text-primary mb-1">• ${res.calculation_type_label} Aktif</div>
                    <div>• Harga paket penuh sebulan: <b>Rp ${res.package_price_raw.toLocaleString()}</b></div>
                    ${res.discount_amount > 0 ? `<div class="text-danger">• Potongan Diskon Membership: <b>- Rp ${res.discount_amount.toLocaleString()}</b></div>` : ''}
                    <hr class="my-1 border-secondary-subtle">
                    <div class="text-dark fw-bold">Total Biaya Bulan Pertama: <span class="text-primary fs-5">Rp ${res.price_final.toLocaleString()}</span></div>
                </div>
            `;
                            }

                            prorateSummaryText.innerHTML = templateText;
                        })
                        .catch(error => {
                            console.error("Error calculating prorate:", error);
                        });
                }

                // ===================================
                // LOAD DURATIONS
                // ===================================
                packageDropdown.addEventListener('change', () => {
                    const id = packageDropdown.value;

                    if (!id) {
                        durationWrapper.classList.add('d-none');
                        durationSelect.innerHTML = '';
                        return;
                    }

                    fetch("{{ route('package.durations') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                package_id: id
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            durationSelect.innerHTML = '<option value="">{!! __('customer.wizard.placeholder.select_duration') !!}</option>';
                            data.forEach(item => {
                                durationSelect.innerHTML +=
                                    `<option value="${item.id}">${item.duration?.name_en ?? 'Unknown'}</option>`;
                            });
                            durationWrapper.classList.remove('d-none');
                        });
                });

                durationSelect.addEventListener('change', calculateClientProrate);

                // ===================================
                // BILLING OPTION TRIGGER
                // ===================================
                document.querySelectorAll('input[name="billing_option"]').forEach(radio => {
                    radio.addEventListener('change', () => {
                        const wrapper = document.getElementById('periode-wrapper');
                        wrapper.classList.toggle('d-none', radio.value !== 'one_time');
                    });
                });

                prorateRadioYes.addEventListener('change', calculateClientProrate);
                prorateRadioNo.addEventListener('change', calculateClientProrate);

                // ===============================================================
                // AUTO DISCOUNT CONTROLLER & TOGGLE BILLING OPTION
                // ===============================================================
                function updateDiscountAndRecalculate() {
                    const packageId = packageDropdown.value;
                    const memberId = memberDropdown.value;

                    // 💡 Logika Tampilkan/Sembunyikan Billing Option berdasarkan pilihan Member
                    if (memberId !== "") {
                        billingOptionWrapper.classList.remove('d-none');
                    } else {
                        billingOptionWrapper.classList.add('d-none');
                        // Reset radio ke bulanan dan sembunyikan dropdown periode jika member dikembalikan ke Non-member
                        document.querySelector('input[name="billing_option"][value="monthly"]').checked = true;
                        document.getElementById('periode-wrapper').classList.add('d-none');
                    }

                    if (!packageId) {
                        discountDisplay.value = '';
                        currentDiscountValue = 0;
                        calculateClientProrate();
                        return;
                    }

                    fetch("{{ route('package.discount') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                package_id: packageId,
                                member_id: memberId
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            discountDisplay.value =
                                `Rp ${(data.effective_discount ?? 0).toLocaleString()} (${data.effective_percent ?? 0}%)`;

                            currentDiscountValue = data.effective_discount ?? 0;
                            calculateClientProrate();
                        });
                }

                packageDropdown.addEventListener('change', updateDiscountAndRecalculate);
                memberDropdown.addEventListener('change', updateDiscountAndRecalculate);
            });
        </script>
    @endsection

</x-layouts.cpanel.customers.app-layout>
