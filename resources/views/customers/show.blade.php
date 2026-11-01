<x-layouts.cpanel.customers.app-layout :title="__('customer.detail.title')">

    <div class="card shadow-sm border-0">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white border-bottom-0">
            <h5 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-user-circle mr-1 text-secondary"></i>
                {{ __('customer.title.show') }}
            </h5>

            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa fa-arrow-left mr-1"></i> {{ __('customer.button.back') }}
            </a>
        </div>

        @if (session('billing_update_result'))
            <div class="alert alert-info">
                <strong>{{ __('customer.info.billing_update_result') }}:</strong>
                <ul class="mb-0 mt-2">
                    @foreach (session('billing_update_result') as $key => $item)
                        <li>
                            <strong>{{ ucfirst($key) }}:</strong>

                            @if ($item['status'] === 'SUCCESS')
                                <span class="badge badge-success">{{ __('customer.info.success') }}</span>
                            @elseif($item['status'] === 'SKIPPED')
                                <span class="badge badge-secondary">{{ __('customer.info.skipped') }}</span>
                            @elseif($item['status'] === 'BLOCKED')
                                <span class="badge badge-warning text-dark">{{ __('customer.info.blocked') }}</span>
                                <div class="small text-muted ml-2">
                                    {{ $item['reason'] ?? '' }}
                                </div>
                            @elseif($item['status'] === 'FAILED')
                                <span class="badge badge-danger">{{ __('customer.info.failed') }}</span>
                                <div class="small text-danger ml-2">
                                    {{ $item['reason'] ?? '' }}
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card-body">

            {{-- TAB HEADER --}}
            <ul class="nav nav-tabs modern-tabs" id="customerTab">
                <li class="nav-item">
                    <button class="nav-link active" data-tab="info">
                        <i class="fas fa-id-card mr-1"></i> {{ __('customer.tabs.info') }}
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" data-tab="billing">
                        <i class="fas fa-receipt mr-1"></i> {{ __('customer.tabs.billing') }}
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" data-tab="server">
                        <i class="fas fa-server mr-1"></i> {{ __('customer.tabs.server') }}
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" data-tab="ont">
                        <i class="fas fa-network-wired mr-1"></i> {{ __('customer.tabs.ont') }}
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" data-tab="traffic">
                        <i class="fas fa-chart-line mr-1"></i> {{ __('customer.tabs.traffic') }}
                    </button>
                </li>
            </ul>

            {{-- TAB CONTENT --}}
            <div id="tab-content" class="mt-4">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div>{{ __('customer.info.loading') }}</div>
                </div>
            </div>

        </div>
    </div>

    @section('custom_styles')
        <style>
            /* Tabs redesign */
            .modern-tabs .nav-link {
                border: none !important;
                padding: 8px 18px;
                margin-right: 6px;
                border-radius: 6px;
                background: #f8f9fa;
                font-weight: 500;
                transition: all .2s ease-in-out;
            }

            .modern-tabs .nav-link:hover {
                background: #e9ecef;
            }

            .modern-tabs .nav-link.active {
                background: #0d6efd !important;
                color: white !important;
                box-shadow: 0 2px 6px rgba(13, 110, 253, 0.25);
            }

            #customerTab .nav-link {
                border: none !important;
                background: #f7f9fc;
                color: #555;
                margin-right: 4px;
                border-radius: 6px 6px 0 0;
                padding: 10px 18px;
                font-weight: 600;
            }

            #customerTab .nav-link.active {
                background: #ffffff;
                color: #0d6efd;
                border-bottom: 3px solid #0d6efd !important;
                box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.05);
            }

            #customerTab {
                border-bottom: 1px solid #e5e7eb;
            }

            .wave-loader {
                display: flex;
                justify-content: center;
                align-items: flex-end;
                height: 40px;
                gap: 4px;
                padding: 28px 0;
            }

            .wave-loader span {
                width: 6px;
                height: 10px;
                background: #0d6efd;
                border-radius: 4px;
                display: inline-block;
                animation: wave 1s infinite ease-in-out;
            }

            .wave-loader span:nth-child(2) {
                animation-delay: .1s;
            }

            .wave-loader span:nth-child(3) {
                animation-delay: .2s;
            }

            .wave-loader span:nth-child(4) {
                animation-delay: .3s;
            }

            .wave-loader span:nth-child(5) {
                animation-delay: .4s;
            }

            @keyframes wave {

                0%,
                100% {
                    height: 10px;
                    opacity: .5;
                }

                50% {
                    height: 35px;
                    opacity: 1;
                }
            }

            .btn-soft {
                border: none;
                border-radius: 10px;
                padding: 8px 22px;
                font-size: 15px;
                font-weight: 600;
                color: #fff;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                cursor: pointer;
                transition: 0.25s ease-in-out;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);
            }

            .btn-blue {
                background-color: #0d6efd !important;
                color: #ffffff;
            }

            .btn-blue:hover {
                background: linear-gradient(145deg, #3e8bff, #0d6efd);
                color: #ffffff;
                transform: translateY(-1px);
                box-shadow: 0 3px 8px rgba(13, 110, 253, 0.35);
            }

            .btn-green {
                background-color: #28a745 !important;
            }

            .btn-green:hover {
                background: linear-gradient(145deg, #41c95b, #28a745) !important;
                transform: translateY(-1px);
                box-shadow: 0 3px 8px rgba(40, 167, 69, 0.35);
            }

            .btn-red {
                background-color: #dc3545 !important;
            }

            .btn-red:hover {
                background: linear-gradient(145deg, #ff5c6b, #dc3545) !important;
                transform: translateY(-1px);
                box-shadow: 0 3px 8px rgba(220, 53, 69, 0.35);
            }

            .btn-gray {
                background-color: #7f8c8d !important;
            }

            .btn-gray:hover {
                background: linear-gradient(145deg, #9aa5a6, #7f8c8d) !important;
                transform: translateY(-1px);
                box-shadow: 0 3px 8px rgba(127, 140, 141, 0.35);
            }

            .btn-soft i {
                font-size: 14px;
            }

            .action-group>* {
                margin-right: 12px;
            }

            .action-group>*:last-child {
                margin-right: 0;
            }

            .action-group-lg>* {
                margin-right: 20px;
            }

            .action-group-lg>*:last-child {
                margin-right: 0;
            }
        </style>
    @endsection

    @section('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tabContent = document.getElementById('tab-content');
                const customerSlug = "{{ $customer->slug }}";

                function showLoader() {
                    tabContent.innerHTML = `
                    <div class="wave-loader">
                        <span></span><span></span><span></span><span></span><span></span>
                    </div>
                `;
                }

                function getQueryParam(name) {
                    const params = new URLSearchParams(window.location.search);
                    return params.get(name);
                }

                let customerMapInstance = null;
                let customerEditMapInstance = null;

                /**
                 * Modul Inisialisasi Peta Leaflet khusus untuk Mode View (Tab Info)
                 */
                function initCustomerMap() {
                    const mapContainer = document.getElementById('customer-map');
                    if (!mapContainer || typeof L === 'undefined') return;

                    const lat = parseFloat(mapContainer.dataset.lat || "{{ $customer->latitude ?? '' }}");
                    const lng = parseFloat(mapContainer.dataset.lng || "{{ $customer->longitude ?? '' }}");
                    const name = mapContainer.dataset.name || "{{ $customer->name ?? '' }}";
                    const address = mapContainer.dataset.address || "{{ $customer->address ?? '' }}";

                    if (isNaN(lat) || isNaN(lng)) return;

                    if (customerMapInstance) {
                        customerMapInstance.remove();
                        customerMapInstance = null;
                    }

                    customerMapInstance = L.map('customer-map').setView([lat, lng], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(customerMapInstance);

                    L.marker([lat, lng]).addTo(customerMapInstance)
                        .bindPopup(`<b>${name}</b><br>${address}`)
                        .openPopup();

                    setTimeout(() => {
                        customerMapInstance?.invalidateSize();
                    }, 250);
                }

                /**
                 * Modul Inisialisasi Peta Interaktif Leaflet khusus untuk Mode Edit (Tab Info Edit)
                 */
                function initCustomerEditMap() {
                    const mapContainer = document.getElementById('customer-edit-map');
                    if (!mapContainer || typeof L === 'undefined') return;

                    const latInput = document.getElementById('edit-latitude');
                    const lngInput = document.getElementById('edit-longitude');

                    let lat = parseFloat(mapContainer.dataset.lat || latInput?.value || '-7.797068');
                    let lng = parseFloat(mapContainer.dataset.lng || lngInput?.value || '110.370529');

                    if (isNaN(lat)) lat = -7.797068;
                    if (isNaN(lng)) lng = 110.370529;

                    if (customerEditMapInstance) {
                        customerEditMapInstance.remove();
                        customerEditMapInstance = null;
                    }

                    customerEditMapInstance = L.map('customer-edit-map').setView([lat, lng], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(customerEditMapInstance);

                    let marker = L.marker([lat, lng], {
                        draggable: true
                    }).addTo(customerEditMapInstance);

                    function updateCoordinates(newLat, newLng) {
                        if (latInput) latInput.value = Number(newLat).toFixed(8);
                        if (lngInput) lngInput.value = Number(newLng).toFixed(8);
                    }

                    if (latInput && !latInput.value) latInput.value = lat.toFixed(8);
                    if (lngInput && !lngInput.value) lngInput.value = lng.toFixed(8);

                    marker.on('dragend', function() {
                        const position = marker.getLatLng();
                        updateCoordinates(position.lat, position.lng);
                    });

                    customerEditMapInstance.on('click', function(e) {
                        marker.setLatLng(e.latlng);
                        updateCoordinates(e.latlng.lat, e.latlng.lng);
                    });

                    function syncFromInputs() {
                        const parsedLat = parseFloat(latInput?.value);
                        const parsedLng = parseFloat(lngInput?.value);
                        if (!isNaN(parsedLat) && !isNaN(parsedLng)) {
                            marker.setLatLng([parsedLat, parsedLng]);
                            customerEditMapInstance.panTo([parsedLat, parsedLng]);
                        }
                    }
                    latInput?.addEventListener('change', syncFromInputs);
                    lngInput?.addEventListener('change', syncFromInputs);

                    const btnCurrentLoc = document.getElementById('btn-edit-current-location');
                    if (btnCurrentLoc) {
                        btnCurrentLoc.addEventListener('click', function() {
                            if ("geolocation" in navigator) {
                                btnCurrentLoc.disabled = true;
                                navigator.geolocation.getCurrentPosition(function(position) {
                                    btnCurrentLoc.disabled = false;
                                    const userLat = position.coords.latitude;
                                    const userLng = position.coords.longitude;
                                    customerEditMapInstance.setView([userLat, userLng], 16);
                                    marker.setLatLng([userLat, userLng]);
                                    updateCoordinates(userLat, userLng);
                                }, function(error) {
                                    btnCurrentLoc.disabled = false;
                                    let errorMsg =
                                        "{{ __('customer.wizard.message.geolocation_failed') }}";
                                    if (error.code === error.PERMISSION_DENIED) {
                                        errorMsg =
                                            "{{ __('customer.wizard.message.geolocation_denied') }}";
                                    }
                                    Swal.fire({
                                        icon: 'error',
                                        title: "{{ __('customer.wizard.message.oops') }}",
                                        text: errorMsg + " (" + error.message + ")",
                                        confirmButtonColor: '#0d6efd'
                                    });
                                }, {
                                    enableHighAccuracy: true,
                                    timeout: 10000
                                });
                            } else {
                                Swal.fire({
                                    icon: 'warning',
                                    title: "{{ __('customer.wizard.message.not_supported') }}",
                                    text: "{{ __('customer.wizard.message.geolocation_unsupported') }}",
                                    confirmButtonColor: '#0d6efd'
                                });
                            }
                        });
                    }

                    setTimeout(() => {
                        customerEditMapInstance?.invalidateSize();
                    }, 250);
                }

                function loadTab(tab) {
                    showLoader();

                    const edit = getQueryParam('edit');
                    let url = `/cpanel/customers/${customerSlug}/tab/${tab}`;

                    if (edit && ['info', 'billing'].includes(tab)) {
                        url += `?edit=${edit}`;
                    }

                    fetch(url)
                        .then(res => {
                            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                            return res.text();
                        })
                        .then(html => {
                            tabContent.innerHTML = html;

                            const tabInitializers = {
                                info: () => {
                                    if (document.getElementById('customer-edit-map')) {
                                        initCustomerEditMap();
                                    } else if (document.getElementById('customer-map')) {
                                        initCustomerMap();
                                    }
                                },
                                billing: () => window.initBillingEditForm?.(),
                                traffic: () => window.initTrafficChart?.(),
                            };

                            tabInitializers[tab]?.();
                        })
                        .catch(error => {
                            console.error('Error loading tab:', error);
                            tabContent.innerHTML = `
                            <div class="alert alert-danger text-center">
                                {{ __('customer.info.load_failed') }}
                            </div>
                        `;
                        });
                }

                const defaultTab = getQueryParam('tab') || 'info';

                document.querySelectorAll('#customerTab .nav-link').forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.tab === defaultTab);
                });

                loadTab(defaultTab);

                document.querySelectorAll('#customerTab .nav-link').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.querySelector('#customerTab .nav-link.active')?.classList.remove(
                            'active');
                        this.classList.add('active');

                        const tab = this.dataset.tab;
                        loadTab(tab);
                    });
                });

            });
        </script>
    @endsection

</x-layouts.cpanel.customers.app-layout>
