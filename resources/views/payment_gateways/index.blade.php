<x-layouts.cpanel.settings.app-layout title="Payment Gateways">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">Payment Gateway</h5>

        <span><a class="btn btn-sm btn-success mt-2" href=""><i class="fa fa-plus"></i> Add</a></span>

    </div>

    <div class="card-body">

        <div class="table-responsive">
            <div style="overflow-x: auto;">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="border-gray-200" width="50px">#</th>
                            <th class="border-gray-200">Name</th>
                            <th class="border-gray-200">Status</th>
                            <th class="border-gray-200">Description</th>
                            <th class="border-gray-200" width="280px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($paymentGateways as $paymentGateway)
                            <tr>
                                <td><span class="fw-normal">{{ ++$i }}</span></td>
                                <td><span class="fw-normal">{{ $paymentGateway->name }}</span></td>
                                <td>
                                    <span id="mikrotik-host" class="fw-normal">
                                        @if ($paymentGateway->disabled === 'false')
                                            {{ __('Enable') }}
                                        @else
                                            {{ __('Disable') }}
                                        @endif
                                    </span>
                                </td>
                                <td><span class="fw-normal">{{ $paymentGateway->description }}</span></td>
                                <td>
                                    @can('server-edit')
                                        <a href="{{ route('paymentgateway.edit', $paymentGateway->slug) }}"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Mikrotik"
                                            class="btn btn-datatable btn-icon btn-transparent-dark me-2 router-edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {!! $paymentGateways->links() !!}
        </div>
        <div class="row align-items-center">
            <div class="col-md-12 mb-3">

                {!! Form::model($webSystem, [
                    'method' => 'PATCH',
                    'route' => ['websystem.updatepaymentgateway', $webSystem->slug],
                ]) !!}
                <div class="card-footer">

                    @if ($webSystem->payment_gateway === 'disable')
                        <button type="submit" class="btn btn-success" id="button-save">
                            <i class="fa fa-check me-2"></i> Enable Payment Gateway
                        </button>
                    @else
                        <button type="submit" class="btn btn-danger" id="button-save">
                            <i class="fa fa-times"></i> Disable Payment Gateway
                        </button>
                    @endif

                </div>
                {!! Form::close() !!}


            </div>
        </div>
    </div>
    @section('custom_scripts')
    @endsection
</x-layouts.cpanel.settings.app-layout>
