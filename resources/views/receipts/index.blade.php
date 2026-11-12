<x-layouts.cpanel.billings.app-layout title="Receipts">

    <div class="card-header py-3">
        <h5 class="mb-2 fw-bold text-primary">{{ __('receipts.header.receipt_list') }}</h5>
        @if (isset($message))
            <div class="alert alert-danger mt-2">
                <a href="#" class="close" data-bs-dismiss="alert" aria-label="close">&times;</a>
                {{ $message }}
            </div>
        @else
            <div class="alert alert-success mt-2">
                <a href="#" class="close" data-bs-dismiss="alert" aria-label="close">&times;</a>
                {{ __('receipts.message.total_receipts', ['count' => $receipts->total()]) }}
            </div>
        @endif
    </div>

    <div class="card-body">
        {{-- Pembungkus Utama Atas: Pagination di kiri, Semua Form/Tombol Filter di kanan --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">

            {{-- Pagination Kiri --}}
            <div>
                {{ $receipts->withQueryString()->links() }}
            </div>

            {{-- Kelompok Filter Kanan (Disatukan dalam inline flex Bootstrap 4) --}}
            <div class="d-flex align-items-center flex-wrap" style="margin-bottom: 15px !important;">

                {{-- === 📑 1. Form Filter Spesifik (Lokal Data via Select2) === --}}
                {!! Form::open([
                    'url' => url()->current(),
                    'method' => 'GET',
                    'class' => 'form-inline me-2 my-1',
                    'id' => 'advancedFilterForm',
                ]) !!}

                {{-- Pertahankan parameter search/pagination lain --}}
                @foreach (request()->except(['filter_payment_method', 'filter_teller', 'filter_date', 'page']) as $key => $value)
                    @if (is_array($value))
                        @foreach ($value as $v)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                <div class="mb-3 me-2 my-1">
                    {!! Form::select('filter_payment_method', $paymentMethods, request('filter_payment_method'), [
                        'placeholder' => __('receipts.placeholder.select_payment_method'),
                        'id' => 'filterPaymentMethod',
                        'class' => 'form-control form-control-sm' /* Bersih tanpa select2-local */,
                        'style' => 'width: 220px;' /* Panjang statis mengunci data terpanjang */,
                    ]) !!}
                </div>

                <div class="mb-3 me-2 my-1">
                    {!! Form::select('filter_teller', $tellers, request('filter_teller'), [
                        'placeholder' => __('receipts.placeholder.select_teller'),
                        'id' => 'filterTeller',
                        'class' => 'form-control form-control-sm' /* Bersih tanpa select2-local */,
                        'style' => 'width: 180px;' /* Panjang statis mengunci data terpanjang */,
                    ]) !!}
                </div>

                <div class="mb-3 me-2 my-1">
                    <input type="text" name="filter_date" id="filterDate"
                        class="form-control form-control-sm datetimepicker"
                        placeholder="{{ __('receipts.placeholder.select_date') }}" value="{{ request('filter_date') }}"
                        autocomplete="off" style="width: 150px;">
                </div>

                <button type="submit" class="btn btn-primary btn-sm my-1 me-1">
                    <i class="fas fa-search me-1"></i> {{ __('receipts.button.filter') }}
                </button>

                @if (request('filter_payment_method') || request('filter_teller') || request('filter_date'))
                    <a href="{{ url()->current() }}"
                        class="btn btn-secondary btn-sm my-1">{{ __('receipts.button.reset') }}</a>
                @endif

                {!! Form::close() !!}

                {{-- === 📤 2. Fitur Kelompok Tombol Export === --}}
                @canany(['receipt-export-all', 'receipt-export-selected-period'])

                    @can('receipt-export-selected-period')
                        {{-- Export per bulan --}}
                        <form action="{{ route('receipts.export.selected') }}" method="GET" class="form-inline me-2 my-1">
                            {!! Form::select('period', $periods, request('period'), [
                                'placeholder' => __('receipts.placeholder.periode'),
                                'class' => 'form-control form-control-sm',
                                'style' => 'width:auto;',
                                'onchange' => 'this.form.submit()',
                            ]) !!}
                        </form>
                    @endcan

                    @can('receipt-export-all')
                        {{-- Export semua --}}
                        <form action="{{ route('receipts.export.all') }}" method="GET" class="form-inline my-1">
                            <input type="hidden" name="all" value="1">
                            <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center"
                                style="height: 31px;">
                                <i class="fas fa-file-excel me-2"></i>{{ __('receipts.button.export_all') }}
                            </button>
                        </form>
                    @endcan

                @endcanany
            </div>
        </div>

        @if ($totalPrice > 0)
            <div class="alert alert-success mt-2">
                <strong> {{ __('receipts.label.total_payments') }}: @moneyIDR($totalPrice) </strong>
            </div>
        @endif

        @if ($receipts->isNotEmpty())

            {{-- ================================================================= --}}
            {{-- 🖥️ TAMPILAN DESKTOP: Berupa Tabel (Sembunyi di layar < md) --}}
            {{-- ================================================================= --}}
            <div class="d-none d-md-block mt-3" style="overflow-x: auto;">
                <table id="billingsTable" cellspacing="0" width="100%"
                    class="table table-bordered table-striped table-hover">
                    <tr class="table-primary text-center">
                        <th>{{ __('receipts.table.no') }}</th>
                        <th>{{ __('receipts.table.customer_name') }}</th>
                        <th>{{ __('receipts.table.customer_id') }}</th>
                        <th>{{ __('receipts.table.address') }}</th>
                        <th>{{ __('receipts.table.periode') }}</th>
                        <th>{{ __('receipts.table.bill') }}</th>
                        <th>{{ __('receipts.table.payment_method') }}</th>
                        <th>{{ __('receipts.table.teller_name') }}</th>
                        <th>{{ __('receipts.table.actions') }}</th>
                    </tr>
                    @foreach ($receipts as $key => $data)
                        <tr>
                            <td><span class="badge text-primary">{{ $receipts->firstItem() + $key }}</span></td>
                            <td>
                                <a href="{{ route('receipts.show', $data->slug) }}" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Show Billing">
                                    <span
                                        class="badge text-primary">{{ $data->customer_name ?? __('receipts.label.no_name') }}</span>
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('receipts.show', $data->slug) }}" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Show Billing">
                                    <span
                                        class="badge text-primary">{{ $data->customer->customer_id ?? __('receipts.label.no_customer_id') }}</span>
                                </a>
                            </td>
                            <td>
                                <span class="badge text-primary"
                                    style="white-space: normal; text-align: left; display: inline-block; max-width: 220px; line-height: 1.4;">
                                    {{ $data->customer_address ?? __('receipts.label.no_address') }}
                                </span>
                            </td>
                            <td>
                                <span
                                    class="badge text-primary">{{ Carbon\Carbon::parse($data->periode)->translatedFormat('M Y') ?? __('receipts.label.no_periode') }}</span>
                            </td>
                            <td>
                                <span class="badge bg-blue-soft text-primary">
                                    @if ($data->package_price === 0)
                                        {{ __('receipts.label.free') }}
                                    @else
                                        @moneyIDR($data->package_price)
                                    @endif
                                </span>
                            </td>
                            <td>
                                @php
                                    $method = match ($data->payment_methode) {
                                        null => __('receipts.label.no_payment_method'),
                                        'cash' => __('receipts.label.cash'),
                                        'transfer' => __('receipts.label.transfer'),
                                        default => $data->payment_methode,
                                    };
                                    $isInline = $data->payment_methode === 'cash';
                                    $formattedDate = \Carbon\Carbon::parse($data->payment_time)->translatedFormat(
                                        'd M Y H:i \W\I\B',
                                    );

                                    // Normalisasi nama bank untuk mempermudah pengecekan warna (lowercase)
                                    $bankKey = strtolower(trim($data->bank_name));

                                    // Tentukan style warna khusus berdasarkan brand bank
                                    $bankStyle = match (true) {
                                        str_contains($bankKey, 'bca')
                                            => 'color: #0056a3; font-weight: bold;', // Biru Khas BCA
                                        str_contains($bankKey, 'mandiri')
                                            => 'color: #fdbb11; font-weight: bold;', // Kuning Emas Mandiri
                                        str_contains($bankKey, 'dana')
                                            => 'color: #118eea; font-weight: bold;', // Biru Terang DANA
                                        default => 'font-weight: bold; color: #495057;',
                                    };
                                @endphp

                                {{-- 1. Badge Status Utama (Hijau hanya untuk indikator sukses & metode) --}}
                                <span class="badge bg-success text-white">
                                    <i class="fas fa-check-circle me-1"></i> {{ $method }}
                                </span>

                                {{-- 2. Detail Waktu & Informasi Bank --}}
                                @if ($isInline)
                                    <div class="small text-muted mt-1">{{ $formattedDate }}</div>
                                @else
                                    <div class="small text-muted mt-1">{{ $formattedDate }}</div>

                                    @if ($data->payment_methode === 'transfer')
                                        <div class="mt-1" style="font-size: 13px; line-height: 1.4;">
                                            {{-- Nama Bank dengan warna khusus --}}
                                            <span style="{{ $bankStyle }}">{{ $data->bank_name }}</span>
                                            <span class="text-secondary">- {{ $data->account_number }}</span>
                                        </div>
                                        {{-- Baris nama ($data->account_name) sudah dihapus dari sini --}}
                                    @endif
                                @endif
                            </td>
                            <td><span
                                    class="badge text-primary">{{ $data->teller_name ?? __('receipts.label.no_teller') }}</span>
                            </td>
                            <td class="text-center">
                                @can('billing-edit')
                                    <a class="btn btn-success btn-sm shadow-sm"
                                        href="{{ route('receipts.print', $data->slug) }}" target="_blank">
                                        <i class="fa fa-print"></i> {{ __('receipts.button.print') }}
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>

            {{-- ================================================================= --}}
            {{-- 📱 TAMPILAN MOBILE: Berupa Cards List (Sembunyi di layar >= md) --}}
            {{-- ================================================================= --}}
            <div class="d-block d-md-none mt-3">
                @foreach ($receipts as $key => $data)
                    @php
                        $method = match ($data->payment_methode) {
                            null => __('receipts.label.no_payment_method'),
                            'cash' => __('receipts.label.cash'),
                            'transfer' => __('receipts.label.transfer'),
                            default => $data->payment_methode,
                        };
                        $isInline = $data->payment_methode === 'cash';
                        $formattedDate = \Carbon\Carbon::parse($data->payment_time)->translatedFormat(
                            'd M Y H:i \W\I\B',
                        );
                    @endphp

                    <div class="card shadow-sm border-left-primary mb-3">
                        <div class="card-body p-3">
                            {{-- Header Card: No & Nama Pelanggan --}}
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                <div>
                                    <span class="badge bg-primary me-1">#{{ $receipts->firstItem() + $key }}</span>
                                    <a href="{{ route('receipts.show', $data->slug) }}" class="fw-bold text-primary">
                                        {{ $data->customer_name ?? __('receipts.label.no_name') }}
                                    </a>
                                </div>
                                <span class="badge bg-blue-soft text-primary fw-bold">
                                    @if ($data->package_price === 0)
                                        {{ __('receipts.label.free') }}
                                    @else
                                        @moneyIDR($data->package_price)
                                    @endif
                                </span>
                            </div>

                            {{-- Detail Informasi --}}
                            <div class="row style-mobile-content small text-muted">
                                <div class="col-6 mb-1"><strong>{{ __('receipts.table.customer_id') }}:</strong></div>
                                <div class="col-6 mb-1 text-dark">{{ $data->customer->customer_id ?? '-' }}</div>

                                <div class="col-6 mb-1"><strong>{{ __('receipts.table.periode') }}:</strong></div>
                                <div class="col-6 mb-1 text-dark">
                                    {{ Carbon\Carbon::parse($data->periode)->translatedFormat('M Y') }}</div>

                                <div class="col-12 mb-1"><strong>{{ __('receipts.table.address') }}:</strong></div>
                                <div class="col-12 mb-2 text-dark text-justify">{{ $data->customer_address ?? '-' }}
                                </div>

                                <div class="col-12 mb-2">
                                    <strong>{{ __('receipts.table.payment_method') }}:</strong>
                                    <div class="mt-1">
                                        {{-- 1. Badge Status Utama --}}
                                        <span class="badge bg-success text-white text-uppercase"
                                            style="padding: 0.4rem 0.6rem;">
                                            <i class="fas fa-check-circle me-1"></i> {{ $method }}
                                        </span>

                                        @php
                                            // Memastikan normalisasi nama bank untuk deteksi warna brand di mobile
                                            $bankKey = strtolower(trim($data->bank_name));

                                            $bankStyle = match (true) {
                                                str_contains($bankKey, 'bca')
                                                    => 'color: #0056a3; font-weight: bold;', // Biru BCA
                                                str_contains($bankKey, 'mandiri')
                                                    => 'color: #fdbb11; font-weight: bold;', // Kuning Emas Mandiri
                                                str_contains($bankKey, 'dana')
                                                    => 'color: #118eea; font-weight: bold;', // Biru DANA
                                                default => 'font-weight: bold; color: #495057;',
                                            };
                                        @endphp

                                        {{-- 2. Detail Waktu & Informasi Bank (Ukuran font standar bawaan tema) --}}
                                        @if ($isInline)
                                            <div class="text-muted mt-1">
                                                {{ __('receipts.label.on') }} {{ $formattedDate }}
                                            </div>
                                        @else
                                            <div class="text-muted mt-1">
                                                {{ __('receipts.label.on') }} {{ $formattedDate }}
                                            </div>

                                            @if ($data->payment_methode === 'transfer')
                                                <div class="mt-1" style="line-height: 1.4;">
                                                    <span
                                                        class="text-secondary">{{ __('receipts.label.to_account') }}</span>
                                                    {{-- Nama Bank dengan warna khusus brand --}}
                                                    <span style="{{ $bankStyle }}">{{ $data->bank_name }}</span>
                                                    <span class="text-secondary">- {{ $data->account_number }}</span>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div class="col-6"><strong>{{ __('receipts.table.teller_name') }}:</strong></div>
                                <div class="col-6 text-dark text-end"><span>{{ $data->teller_name ?? '-' }}</span>
                                </div>
                            </div>

                            {{-- Tombol Aksi Mobile --}}
                            @can('billing-edit')
                                <div class="border-top pt-2 mt-2 text-end">
                                    <a class="btn btn-success btn-sm w-100 shadow-sm"
                                        href="{{ route('receipts.print', $data->slug) }}" target="_blank">
                                        <i class="fa fa-print me-1"></i> {{ __('receipts.button.print') }}
                                    </a>
                                </div>
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-danger mt-2">
                {{ __('receipts.message.no_receipts') }}
            </div>
        @endif

        <div class="mt-3">
            {{ $receipts->withQueryString()->links() }}
        </div>
    </div>

    @section('custom_scripts')
        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

        <script>
            $(document).ready(function() {
                // Inisialisasi Datepicker tetap dipertahankan
                $('#filterDate').datepicker({
                    format: 'yyyy-mm-dd',
                    autoclose: true,
                    todayHighlight: true
                });
            });
        </script>
    @endsection
</x-layouts.cpanel.billings.app-layout>
