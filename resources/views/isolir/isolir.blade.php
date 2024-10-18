@extends('layouts.isolir')
@section('content')
<!-- Outer Row -->
<div class="row justify-content-center">
    <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="col-lg-6 d-none d-lg-block bg-isolir-image">
                </div>
                <div class="row">
                    <div class="col-lg-13">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h1 mb-3"><span class="badge rounded-pill bg-danger text-white">{{ __('Akun anda terisolir!') }}</span></h1>
                            </div>
                            <div class="card-header py-3">
                                <h5 class="m-0 font-weight-bold text-primary">{{ __('Hallo customer ShaNet Fiber,') }}</h5>
                            </div>
                            <div class="card-body">
                                Mohon maaf. Dengan berat hati kami sampaikan bahwa akun
                                anda saat ini sedang terisolir otomatis oleh system kami.
                                Untuk dapat kembali menggunakan layanan internet kami,
                                silahkan lakukan pembayaran paket berlangganan internet anda.
                                <br><br>
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
@endsection