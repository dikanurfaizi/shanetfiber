<div class="card-header py-3">
    <h5 class="m-0 font-weight-bold text-primary">{{ __('customer.header.show-server') }}</h5>

    <span class="text-danger">{{ $info_msg }}</span>
</div>

<div class="card-body col-lg-8">
    <table class="table no-footer">
        <tr>
            <td><strong>{{ __('customer.label.server') }}</strong></td>
            <td>: {{ $customer->mikrotik->name }}</td>

            <td><strong>{{ __('customer.label.profile') }}</strong></td>
            <td>: {{ $customer->active_package->profile ?? '-' }}</td>
        </tr>

        <tr>
            <td><strong>{{ __('customer.label.service') }}</strong></td>
            <td>: {{ $customer->pppType->name ?? 'No Service' }}</td>

            <td><strong>{{ __('customer.label.state') }}</strong></td>
            <td>:
                @if ($customer->activation == 'false')
                    <span class="badge bg-danger text-white">
                        {{ __('customer.info.not-activation') }}
                    </span>
                @else
                    <span class="badge bg-primary text-white">{{ __('Active') }}</span>
                @endif
            </td>
        </tr>

        <tr>
            <td><strong>{{ __('customer.label.username') }}</strong></td>
            <td>: {{ $customer->username }}</td>

            <td><strong>{{ __('customer.label.password-ppp') }}</strong></td>
            <td>: {{ $customer->password_ppp }}</td>
        </tr>
    </table>
</div>
