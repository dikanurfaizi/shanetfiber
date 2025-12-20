<x-layouts.cpanel.billings.app-layout title="Billing Transfer">

    {!! Form::model($billing, [
        'method' => 'PATCH',
        'route' => ['billings.payment', $billing->slug],
    ]) !!}

    <div class="card-header py-3">

        {{-- =======================
    HEADER
    ======================== --}}
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white border-bottom-0">
            <h5 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-file-invoice me-1 text-secondary"></i>
                {{ __('billing.bank-transfer') }}
            </h5>

            <a href="{{ route('billings.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="row g-4">

            {{-- =======================
        BILLING SUMMARY
        ======================== --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">

                        <h6 class="text-muted mb-4">Billing Information</h6>

                        {{-- PRIMARY INFO --}}
                        <div class="row g-4 mb-3">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">Billing Number</span>
                                    <span class="info-value fw-semibold">
                                        {{ $billing->billing_number }}
                                    </span>
                                </div>

                                <div class="info-item">
                                    <span class="info-label">Customer</span>
                                    <span class="info-value">
                                        {{ $billing->customer->name }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6 text-md-end">
                                <span class="info-label d-block">Total Amount</span>
                                <span class="fs-4 fw-bold text-primary">
                                    @moneyIDR($billing->base_price)
                                </span>
                            </div>
                        </div>

                        <hr class="my-3">

                        {{-- SECONDARY INFO --}}
                        <div class="row g-4 mb-3">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">Periode</span>
                                    <span class="info-value">
                                        {{ $billing->billingSubscription->billingPeriode->name }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-item">
                                    <span class="info-label">Package</span>
                                    <span class="info-value">
                                        {{ $billing->billingSubscription->customerSubscription->package->name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">

                        {{-- META INFO (STATUS DI SINI) --}}
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <span class="info-label">Created By</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-user"></i>
                                        {{ $billing->createdByUser->name }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <span class="info-label">Teller</span>
                                <div>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-id-card me-1"></i>
                                        {{ $billing->billingPayment->paidByUser->name ?? '-' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <span class="info-label">Status</span>
                                <div>
                                    @if ($billing->status === 'LS')
                                        <span class="badge bg-success text-white px-3 py-1">
                                            <i class="fas fa-check-circle me-1"></i> Paid
                                        </span>
                                    @elseif ($billing->status === 'BL')
                                        <span class="badge bg-danger text-white px-3 py-1">
                                            <i class="fas fa-times-circle me-1"></i> Unpaid
                                        </span>
                                    @elseif ($billing->status === 'PL')
                                        <span class="badge bg-warning text-white px-3 py-1">
                                            <i class="fas fa-hourglass-half me-1"></i> Pending
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =======================
                 PAYMENT FORM
            ======================== --}}
            @if ($billing->status !== 'LS')
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">

                            <h6 class="text-muted mb-4">Transfer Information</h6>

                            <div class="row align-items-stretch">

                                <div class="col-12 mb-3">
                                    <div class="info-item">
                                        <label class="info-label">Bank Transfer</label>
                                        <select name="bank_transfer" class="form-control">
                                            <option value="">Select Bank</option>
                                            @foreach ($banks as $bank)
                                                <option value="{{ $bank->id }}">
                                                    {{ $bank->name }} ({{ $bank->bank }} -
                                                    {{ $bank->account_number }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="info-item">
                                        <label class="info-label">Deposit Bank</label>
                                        {!! Form::text('deposit_bank_name', null, [
                                            'class' => 'form-control',
                                            'placeholder' => 'Bank Name',
                                        ]) !!}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="info-item">
                                        <label class="info-label">Account Name</label>
                                        {!! Form::text('deposit_account_name', null, [
                                            'class' => 'form-control',
                                            'placeholder' => 'Account Holder',
                                        ]) !!}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="info-item">
                                        <label class="info-label">Account Number</label>
                                        {!! Form::text('deposit_account_number', null, [
                                            'class' => 'form-control',
                                            'placeholder' => 'Account Number',
                                        ]) !!}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="info-item">
                                        <label class="info-label">Reference ID</label>
                                        {!! Form::text('reference_id', null, [
                                            'class' => 'form-control',
                                            'placeholder' => 'Transfer Reference',
                                        ]) !!}
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="card-footer bg-white border-0 d-flex justify-content-end">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-money-bill-wave me-1"></i>
                                Pay Now
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <input type="hidden" name="transfer_payment" value="transfer_payment">

        {!! Form::close() !!}

        @section('custom-styles')
            <style>
                .info-item {
                    display: flex;
                    flex-direction: column;
                    gap: 2px;
                }

                .info-label {
                    font-size: 12px;
                    color: #6c757d;
                }

                .info-value {
                    font-size: 14px;
                    font-weight: 500;
                    color: #212529;
                }
            </style>
        @endsection

</x-layouts.cpanel.billings.app-layout>
