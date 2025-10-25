@extends('layouts.isolir')
@section('content')
<!-- Outer Row -->
<div class="row justify-content-center">
    <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-13">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h1 mb-3"><span class="badge rounded-pill bg-danger text-white">{{ __('Akun anda terisolir!') }}</span></h1>
                            </div>
                            <h5 class="m-0 font-weight-bold text-primary">{{ __('isolir.welcome', ['name' => $customer->name]) }}</h5>
                            <div class="card-body">
                                Akun anda saat ini sedang terisolir secara otomatis oleh system kami. Untuk dapat menikmati layanan internet kami,
                                silahkan melakukan pembayaran paket berlangganan internet anda.<br>
                                Anda mempunyai tagihan:<br>
                                <div class="card-header col-lg-6">
                                    @foreach ($billings as $billing)
                                    <div class="text-primary">
                                        {{$loop->iteration}}. {{ $billing->billing_periode->name }}
                                        <span class="text-danger">@moneyIDR($billing->base_price)</span>
                                    </div>
                                    @endforeach
                                    <br><br>Jumlah tagihan yang harus di bayar <i class="badge bg-danger-soft text-danger">@moneyIDR($billings->sum('base_price'))</i>
                                </div>

                                <div class="card-body col-lg-6">
                                    Pembayaran dapat dilakukan dengan cara:<br>
                                    1. Cash/Tunai.<br>
                                    2. Transfer ke rekening kami atas nama <span class="badge bg-primary text-white">Dian Ardiansyah</span><br><br>
                                    - <span class="badge bg-success text-white">Bank Mandiri 1730012695905</span><br>
                                    - <span class="badge bg-success text-white">Bank BCA 2990407315</span><br>
                                    - <span class="badge bg-success text-white">DANA 085795909904</span><br>
                                    <br>

                                    Silahkan melakukan konfirmasi ke nomor <span class="badge bg-primary text-white">087818040024</span> setelah
                                    melakukan pembayaran dengan mengirimkan bukti transfer.
                                    <br>
                                    Jika anda sudah melakukan pembayaran, dan muncul halaman ini
                                    silahkan segera menghubungi kami.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection