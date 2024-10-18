<x-layouts.cpanel.customers.app-layout title="{{ __('customer.title.delete') }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('customer.header.delete') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::open(['method' => 'DELETE','route' => ['customers.destroy', $customer->slug]]) !!}
            <div class="card-body col-lg-10">
                <div class="col-md-12 mb-3">
                    <div class="alert alert-danger">
                        <strong>{{ __('Are you sure to delete customer?') }}</strong><br><br>
                        {{ __('Name : ') }}{{$customer->name }}<br>
                        {{ __('Address : ') }}{{$customer->address }}<br>
                        {{ __('Server : ') }}{{$customer->mikrotik->name }}<br>
                        {{ __('Deleting a customer will delete their charges and ') }}&nbsp<strong>{{ __('user secret') }}</strong>&nbsp{{ __('on your server ') }}&nbsp<strong>{{ $customer->mikrotik->name }}</strong>.<br>
                        {{ __('After delete, this customer cannot connect to internet service.') }}<br><br>
                        {{ __('Press') }}
                        <i class="fa fa-trash me-2"></i>&nbsp<strong>{{ __('customer.action.delete') }}</strong>
                        {{ __('to delete this customer.') }}<br>

                        {{ __('Press') }}
                        <i class="fa fa-arrow-left"></i>&nbsp<strong>{{ __('customer.button.cancel') }}</strong>
                        {{ __('to cancel this operation.') }}
                    </div>
                </div>

                <div class="card-footer">
                    @can('customer-delete')
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash me-2"></i>&nbsp{{ __('customer.action.delete') }}</button>
                    @endcan
                    <a class="btn btn-sm btn-secondary" href="{{ URL::previous() }}"><i class="fa fa-arrow-left"></i>&nbsp{{ __('customer.button.cancel') }}</a>
                </div>
                {!! Form::close() !!}

            </div>
        </div>
</x-layouts.cpanel.customers.app-layout>