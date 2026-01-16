<x-layouts.cpanel.customers.app-layout :title="'Detail Customer'">

    <div class="card shadow-sm border-0">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white border-bottom-0">
            <h5 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-user-circle me-1 text-secondary"></i>
                Detail Customer
            </h5>

            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>

        @if (session('billing_update_result'))
            <div class="alert alert-info">
                <strong>Billing Update Result:</strong>
                <ul class="mb-0 mt-2">
                    @foreach (session('billing_update_result') as $key => $item)
                        <li>
                            <strong>{{ ucfirst($key) }}:</strong>

                            @if ($item['status'] === 'SUCCESS')
                                <span class="badge bg-success text-white">Success</span>
                            @elseif($item['status'] === 'SKIPPED')
                                <span class="badge bg-secondary text-white">Skipped</span>
                            @elseif($item['status'] === 'BLOCKED')
                                <span class="badge bg-warning text-white">Blocked</span>
                                <div class="small text-muted ms-2">
                                    {{ $item['reason'] ?? '' }}
                                </div>
                            @elseif($item['status'] === 'FAILED')
                                <span class="badge bg-danger">Failed</span>
                                <div class="small text-danger ms-2">
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
                        <i class="fas fa-id-card me-1"></i> Info
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" data-tab="billing">
                        <i class="fas fa-receipt me-1"></i> Billing
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" data-tab="server">
                        <i class="fas fa-server me-1"></i> Server
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" data-tab="ont">
                        <i class="fas fa-network-wired me-1"></i> ONT
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" data-tab="traffic">
                        <i class="fas fa-chart-line me-1"></i> Traffic
                    </button>
                </li>
            </ul>

            {{-- TAB CONTENT --}}
            <div id="tab-content" class="mt-4">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div>Loading...</div>
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


            /* ============================
                                                                                                                                   BLUE BUTTON (EDIT)
                                                                                                                                   ============================ */
            .btn-blue {
                background-color: #0d6efd !important;
                /* base blue */
                color: #ffffff;
            }

            /* Hover-nya lebih cerah, bukan gelap */
            .btn-blue:hover {
                background: linear-gradient(145deg,
                        #3e8bff,
                        #0d6efd);
                color: #ffffff;
                /* tetap putih, lebih bright */
                transform: translateY(-1px);
                box-shadow: 0 3px 8px rgba(13, 110, 253, 0.35);
            }

            /* ============================
                                                                                                                           GREEN BUTTON (Activation / Enable)
                                                                                                                           ============================ */
            .btn-green {
                background-color: #28a745 !important;
                /* iOS-green */
            }

            .btn-green:hover {
                background: linear-gradient(145deg,
                        #41c95b,
                        #28a745) !important;
                transform: translateY(-1px);
                box-shadow: 0 3px 8px rgba(40, 167, 69, 0.35);
            }


            /* ============================
                                                                                                                           RED BUTTON (Disable)
                                                                                                                           ============================ */
            .btn-red {
                background-color: #dc3545 !important;
                /* Bootstrap red */
            }

            .btn-red:hover {
                background: linear-gradient(145deg,
                        #ff5c6b,
                        #dc3545) !important;
                transform: translateY(-1px);
                box-shadow: 0 3px 8px rgba(220, 53, 69, 0.35);
            }


            /* ============================
                                                                                                                           GRAY BUTTON (Unavailable / Secondary)
                                                                                                                           ============================ */
            .btn-gray {
                background-color: #7f8c8d !important;
                /* soft gray */
            }

            .btn-gray:hover {
                background: linear-gradient(145deg,
                        #9aa5a6,
                        #7f8c8d) !important;
                transform: translateY(-1px);
                box-shadow: 0 3px 8px rgba(127, 140, 141, 0.35);
            }

            /* OPTIONAL icon size */
            .btn-soft i {
                font-size: 14px;
            }

            /* ======================================
                                                                                                    BUTTON / ACTION GROUP SPACING
                                                                                                    ====================================== */
            .action-group>* {
                margin-right: 12px;
            }

            .action-group>*:last-child {
                margin-right: 0;
            }

            /* Variant lebih renggang */
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

                function loadTab(tab) {
                    showLoader();

                    const edit = getQueryParam('edit');

                    let url = `/cpanel/customers/${customerSlug}/tab/${tab}`;

                    if (edit && ['info', 'billing'].includes(tab)) {
                        url += `?edit=${edit}`;
                    }

                    fetch(url)
                        .then(res => {
                            // console.log('FETCH STATUS:', res.status);
                            // console.log('FETCH REDIRECTED:', res.redirected);
                            return res.text();
                        })
                        .then(html => {
                            // console.log('TAB VALUE:', tab);
                            // console.log('AVAILABLE INITIALIZERS:', Object.keys(tabInitializers));
                            tabContent.innerHTML = html;

                            // 🔥 INIT AFTER HTML INJECTED
                            const tabInitializers = {
                                billing: () => window.initBillingEditForm?.(),
                                // info: () => window.initCustomerInfo?.(),
                                // server: () => window.initCustomerServer?.(),
                                traffic: () => window.initTrafficChart?.(),
                            };

                            tabInitializers[tab]?.();
                        })
                        .catch(() => {
                            tabContent.innerHTML =
                                '<div class="alert alert-danger text-center">Failed to load content.</div>';
                        });

                }

                // Load default tab
                const defaultTab = getQueryParam('tab') || 'info';
                loadTab(defaultTab);

                // set active tab
                document.querySelectorAll('#customerTab .nav-link').forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.tab === defaultTab);
                });

                // Tab click handler
                document.querySelectorAll('#customerTab .nav-link').forEach(btn => {
                    btn.addEventListener('click', function() {

                        document.querySelector('#customerTab .nav-link.active')
                            ?.classList.remove('active');
                        this.classList.add('active');

                        const tab = this.dataset.tab;
                        loadTab(tab);
                    });
                });
            });
        </script>
    @endsection

</x-layouts.cpanel.customers.app-layout>
