<x-layouts.customers.app-layout title="Billings {{ auth()->guard('customer')->user()->name }}">

    <div class="card-header py-3 bg-gradient-primary">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold text-white">{{ __('Payments') }}</h5>
            <div class="btn-group" role="group">
                <a class="btn btn-sm @if (request()->routeIs('customer.payment.index')) btn-success @else btn-light bg-white-50 @endif"
                    href="{{ route('customer.payment.index') }}">
                    <i class="fas fa-star mr-1"></i>
                    {{ __('billing.button.all-payment') }}
                </a>
                <a class="btn btn-sm @if (Request::is('customer/billings/payments/BL/show')) btn-danger @else btn-light bg-white-50 @endif"
                    href="{{ route('customer.payment.state', 'BL') }}">
                    <i class="fas fa-ban mr-1"></i>
                    {{ __('billing.button.unpaid') }}
                </a>
                <a class="btn btn-sm @if (Request::is('customer/billings/payments/LS/show')) btn-success @else btn-light bg-white-50 @endif"
                    href="{{ route('customer.payment.state', 'LS') }}">
                    <i class="fas fa-heart mr-1"></i>
                    {{ __('billing.button.paid-off') }}
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="fas fa-info-circle mr-2"></i>
            {{ __('Here you can view and manage all your billing payments') }}
        </div>

        <div class="table-responsive">
            <table id="paketsTable" class="table table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col" class="text-center">#</th>
                        <th scope="col">{{ __('customer.table.billing-id') }}</th>
                        <th scope="col">{{ __('customer.table.billing-period') }}</th>
                        <th scope="col" class="text-right">{{ __('customer.table.total-payment') }}</th>
                        <th scope="col" class="text-center">{{ __('customer.table.payment-status') }}</th>
                        <th scope="col" class="text-center">{{ __('customer.table.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($billings as $billing)
                        <tr class="@if ($billing->status != 'LS') table-warning @endif">
                            <td class="text-center">{{ ++$i }}</td>
                            <td><strong>#{{ $billing->billing_number }}</strong></td>
                            <td>{{ $billing->periode_text }}</td>
                            <td class="text-right font-weight-bold">@moneyIDR(ceil($billing->total_amount))</td>
                            <td class="text-center">
                                @if ($billing->status == 'LS')
                                    <span class="badge badge-success p-2">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        {{ __('customer.label.state-lunas') }}
                                    </span>
                                @else
                                    <span class="badge badge-danger p-2">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ __('customer.label.state-belum-lunas') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('customer.payment.show', $billing->id) }}"
                                    class="btn btn-sm btn-primary">
                                    <i class="far fa-eye mr-1"></i>
                                    {{ __('billing.button.view') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $billings->firstItem() }} to {{ $billings->lastItem() }} of {{ $billings->total() }}
                entries
            </div>

            @if ($billings->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $billings->firstItem() }} to {{ $billings->lastItem() }} of
                        {{ $billings->total() }} entries
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <form method="GET" action="{{ url()->current() }}" class="form-inline">
                                @foreach (request()->except('per_page') as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                <label for="per_page" class="mr-2">Show:</label>
                                <select name="per_page" id="per_page" class="form-control form-control-sm"
                                    onchange="this.form.submit()">
                                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10
                                    </option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25
                                    </option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50
                                    </option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100
                                    </option>
                                </select>
                            </form>
                        </div>
                        <div class="pagination-container">
                            {{ $billings->appends(request()->except('page'))->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @section('custom_scripts')
        @if ($message = Session::get('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ $message }}',
                    confirmButtonColor: '#4e73df',
                    background: '#f8f9fc',
                })
            </script>
        @endif

        <script>
            $(document).ready(function() {
                $('#paketsTable').DataTable({
                    responsive: true,
                    searching: false,
                    paging: false, // Disable DataTables pagination
                    info: false, // Disable DataTables info
                    lengthChange: false, // Disable "Show entries" dropdown
                    dom: 'rt', // Only keep table (t) and responsive (r)
                    columnDefs: [{
                            orderable: true,
                            targets: [0, 1, 2, 3]
                        },
                        {
                            orderable: false,
                            targets: [4, 5]
                        }
                    ],
                    initComplete: function() {
                        // Move Laravel pagination into DataTables wrapper
                        $('.pagination-container').appendTo($('#paketsTable_wrapper'));
                    }
                });
            });
        </script>
    @endsection

    @section('custom_css')
        <style>
            .card-header {
                border-radius: 0.35rem 0.35rem 0 0 !important;
            }

            .table thead th {
                border-bottom: 2px solid #e3e6f0;
            }

            .table-hover tbody tr:hover {
                transform: scale(1.01);
                box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            }

            .badge {
                font-size: 0.85em;
                min-width: 100px;
            }

            .pagination .page-item.active .page-link {
                background-color: #4e73df;
                border-color: #4e73df;
            }

            .pagination .page-link {
                color: #4e73df;
            }

            .btn-light.bg-white-50 {
                background-color: rgba(255, 255, 255, 0.8) !important;
                backdrop-filter: blur(5px);
                color: #4e73df;
                border: 1px solid #ddd;
            }

            .btn-light.bg-white-50:hover {
                background-color: rgba(255, 255, 255, 0.9) !important;
            }

            .table-warning {
                background-color: rgba(255, 243, 205, 0.5);
            }

            .table-warning:hover {
                background-color: rgba(255, 243, 205, 0.7);
            }

            .pagination-container {
                display: block !important;
            }

            .pagination {
                margin-bottom: 0;
            }

            #paketsTable_wrapper {
                display: flex;
                flex-direction: column;
            }

            #paketsTable_wrapper .pagination-container {
                order: 2;
            }
        </style>
    @endsection
</x-layouts.customers.app-layout>
