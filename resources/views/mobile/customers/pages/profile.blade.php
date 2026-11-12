<x-layouts.customers.mobile.app-layout title="Customers">

    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('customer.page.header.profile') }}</h5>

    </div>

    <div class="card-body col-lg-12">
        <div style="overflow-x: auto;">
            <table id="serverTable" cellspacing="0" width="100%" class="table no-footer">
                <tr class="table-borderless">
                    <td><strong>{{ __('customer.label.name') }}</strong></td>
                    <td>{{ __(':') }} {{ $customer->name }}</td>
                </tr>
                <tr class="table-borderless">
                    <td><strong>{{ __('customer.label.email') }}</strong></td>
                    <td>{{ __(':') }} {{ $customer->email }}</td>
                </tr>
                <tr class="table-borderless">
                    <td><strong>{{ __('customer.label.contact') }}</strong></td>
                    <td>{{ __(':') }} {{ $customer->contact }}</td>
                </tr>
                <tr class="table-borderless">
                    <td><strong>{{ __('customer.label.address') }}</strong></td>
                    <td>{{ __(':') }} {{ $customer->address }}</td>
                </tr>

            </table>
        </div>
    </div>


</x-layouts.customers.mobile.app-layout>
