@props(['title', 'group', 'channels'])

@php
    $collapseId = 'collapse-' . Str::slug($group); // Buat ID unik berdasarkan nama grup
@endphp

<div class="col-12 mt-2">
    <div class="card shadow mb-2">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold">{{ $title }}</h5>
            <button class="btn btn-light btn-sm d-flex align-items-center toggle-button" type="button"
                data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false"
                aria-controls="{{ $collapseId }}">
                <span class="me-1"><i class="fas fa-chevron-down toggle-icon"></i></span>
            </button>
        </div>
    </div>
    <div id="{{ $collapseId }}" class="collapse hide">
        <ul class="list-group">
            @foreach ($channels as $channel)
                @if ($channel['group'] === $group)
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center">
                                <!-- Logo -->
                                <img src="{{ $channel['icon_url'] }}" alt="" class="img-fluid"
                                    style="width: 50px; height: 35px; object-fit: contain; margin-right: 10px;">

                                <!-- Nama -->
                                <div class="payment-name font-weight-bold">
                                    {{ $channel['name'] === 'Other Bank Virtual Account' ? 'Virtual Account Bank Lain' : $channel['name'] }}
                                </div>
                            </div>

                            <!-- Biaya di bawah logo + nama -->
                            <small class="text-muted mt-1">
                                Biaya:
                                @if ($channel['fee_customer']['flat'] > 0)
                                    Rp {{ number_format($channel['fee_customer']['flat'], 0, ',', '.') }}
                                    @if ($channel['fee_customer']['percent'] > 0)
                                        + {{ number_format($channel['fee_customer']['percent'], 2, ',', '.') }}%
                                    @endif
                                @elseif ($channel['fee_customer']['percent'] > 0)
                                    {{ number_format($channel['fee_customer']['percent'], 2, ',', '.') }}%
                                @else
                                    Gratis
                                @endif
                            </small>
                        </div>

                        <!-- Radio Button dengan Data Attribute -->
                        <input type="radio" name="payment_method" value="{{ $channel['code'] }}"
                            data-icon="{{ $channel['icon_url'] }}" data-group="{{ $channel['group'] }}"
                            class="ml-auto payment-radio">
                    </li>
                @endif
            @endforeach
        </ul>
    </div>
</div>

@section('custom_scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".toggle-button").forEach(button => {
                const icon = button.querySelector(".toggle-icon");
                const targetId = button.getAttribute("data-bs-target"); // Ambil ID collapse
                const content = document.querySelector(targetId);

                if (!content) return;

                // Event saat collapse DIBUKA
                content.addEventListener("show.bs.collapse", function() {
                    icon.classList.replace("fa-chevron-down", "fa-chevron-up");
                });

                // Event saat collapse DITUTUP
                content.addEventListener("hide.bs.collapse", function() {
                    icon.classList.replace("fa-chevron-up", "fa-chevron-down");
                });
            });
        });
    </script>

    <script>
        const orderButton = document.querySelector(".order-button");

        if (orderButton) {
            orderButton.addEventListener("click", function(event) {
                const selectedPayment = document.querySelector('input[name="payment_method"]:checked');

                if (!selectedPayment) {
                    event.preventDefault(); // Mencegah form submit
                    Swal.fire({
                        icon: "warning",
                        title: "Metode Pembayaran Belum Dipilih",
                        text: "Silakan pilih metode pembayaran sebelum melanjutkan!",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#3085d6"
                    });
                }
            });
        }
    </script>

    <script>
        // Update hidden input dengan icon_url saat radio dipilih
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".payment-radio").forEach(radio => {
                radio.addEventListener("change", function() {
                    let iconUrl = this.dataset.icon; // Ambil icon_url dari data-attribute
                    let paymentType = this.dataset.group;
                    document.getElementById("icon_url").value = iconUrl; // Set ke hidden input
                    document.getElementById("payment_type").value = paymentType;
                });
            });
        });
    </script>
@endsection
