<x-layouts.cpanel.customers.app-layout :title="'Detail Customer'">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Step 3: Package & Membership</h5>
        </div>

        <form method="POST" action="{{ route('wizard.step3') }}">
            @csrf

            <div class="card-body">

                {{-- =======================
                SELECT PACKAGE
            ======================== --}}
                <div class="form-group mb-3">
                    <label><strong>Package</strong> <span class="text-danger">*</span></label>
                    <select name="package_id" id="package-dropdown" class="form-control">
                        <option value="">Select Package</option>
                        @foreach ($packages as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
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
                    <label><strong>Duration</strong></label>
                    <select name="package_duration_id" id="duration-select" class="form-control">
                        <option value="">Select Duration</option>
                    </select>
                    @error('package_duration_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- =======================
                MEMBERSHIP
            ======================== --}}
                <div class="form-group mb-3">
                    <label><strong>Membership</strong></label>
                    <select name="member_id" id="member-dropdown" class="form-control">
                        <option value="">Non Member</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}">
                                {{ $member->name }} (Diskon Rp {{ number_format($member->discount_value) }})
                            </option>
                        @endforeach
                    </select>
                    @error('member_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- =======================
                BILLING OPTION
            ======================== --}}
                <div class="form-group mb-3">
                    <label><strong>Billing Option</strong></label><br>

                    <div class="form-check form-check-inline">
                        <input type="radio" name="billing_option" value="monthly" class="form-check-input" checked>
                        <label class="form-check-label">Monthly</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input type="radio" name="billing_option" value="one_time" class="form-check-input">
                        <label class="form-check-label">One-Time Period</label>
                    </div>

                    @error('billing_option')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- =======================
                SELECT BILLING PERIODE (IF ONE TIME)
            ======================== --}}
                <div id="periode-wrapper" class="form-group mb-3 d-none">
                    <label><strong>Select Billing Period</strong></label>
                    <select name="billing_periode_name" id="periode-select" class="form-control">
                        <option value="">-- Select Period --</option>
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
                <div class="form-group">
                    <label><strong>Discount Applied</strong></label>
                    <input type="text" id="discount_display" class="form-control" readonly>
                </div>

            </div>

            <div class="card-footer text-end">
                <a href="{{ route('wizard.step2') }}" class="btn btn-secondary">← Back</a>
                <button class="btn btn-primary">Next Step →</button>
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

                // ===========================
                // LOAD DURATIONS
                // ===========================
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
                            durationSelect.innerHTML = '<option value="">Select Duration</option>';

                            data.forEach(item => {
                                durationSelect.innerHTML += `
                    <option value="${item.id}">
                        ${item.duration?.name_en ?? 'Unknown'}
                    </option>
                `;
                            });

                            durationWrapper.classList.remove('d-none');
                        });
                });

                // ===========================
                // BILLING OPTION — show periode select
                // ===========================
                document.querySelectorAll('input[name="billing_option"]').forEach(radio => {
                    radio.addEventListener('change', () => {
                        const wrapper = document.getElementById('periode-wrapper');
                        wrapper.classList.toggle('d-none', radio.value !== 'one_time');
                    });
                });

                // ===========================
                // AUTO DISCOUNT CALCULATION
                // ===========================
                function updateDiscount() {
                    const packageId = packageDropdown.value;
                    const memberId = memberDropdown.value;

                    if (!packageId) return discountDisplay.value = '';

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
                                `Rp ${data.effective_discount.toLocaleString()} (${data.effective_percent}%)`;
                        });
                }

                packageDropdown.addEventListener('change', updateDiscount);
                memberDropdown.addEventListener('change', updateDiscount);
            });
        </script>
    @endsection

</x-layouts.cpanel.customers.app-layout>
