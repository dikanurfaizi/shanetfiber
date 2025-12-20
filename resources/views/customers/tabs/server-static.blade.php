<div class="card-header py-3">
    <h5 class="m-0 font-weight-bold text-primary">{{ __('customer.header.show-server') }}</h5>

    <div class="text-success">{{ $info_msg }}</div>

    <div class="mt-2">
        IP Address: <strong>{{ $customer->ip_static }}</strong><br>
        MAC Address: <strong>{{ $customer->mac_address }}</strong><br>
        Interface: <strong>{{ $customer->interface }}</strong><br>
        Limitasi Name: <strong>{{ $customer->username }}</strong>
    </div>
</div>
