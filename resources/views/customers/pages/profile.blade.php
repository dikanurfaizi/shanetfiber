<x-layouts.customers.app-layout title="Customers">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('customer.page.header.profile') }}</h5>

    </div>

    <div class="card-body col-lg-12">
        <div class="table-responsive">
            <table id="serverTable" cellspacing="0" width="100%" class="table no-footer">
                <tr class="table-borderless">
                    <td width="5%"><strong>{{ __('customer.label.name') }}</strong></td>
                    <td>{{ __(':') }} {{ $customer->name }}</td>
                </tr>
                <tr>
                    <td width="5%"><strong>{{ __('customer.label.contact') }}</strong></td>
                    <td>{{ __(':') }} {{ $customer->contact }}</td>
                </tr>
                <tr>
                    <td width="5%"><strong>{{ __('customer.label.email') }}</strong></td>
                    <td class="email-column">{{ __(':') }} {{ $customer->email }}</td>
                </tr>
                <tr>
                    <td width="5%"><strong>{{ __('customer.label.address') }}</strong></td>
                    <td>{{ __(':') }} {{ $customer->address }}</td>
                </tr>
            </table>
        </div>
    </div>


</x-layouts.customers.app-layout>