<x-layouts.cpanel.customers.app-layout title="Customers Status">

  <div class="card-header py-3">
    <h5 class="m-0 font-weight-bold text-primary">
      @if ($status === 'offline')
      {{ __('customer.header.offline-customer') }}
      @elseif ($status === 'isolir')
      {{ __('Isolirs') }}
      @else
      {{ __('Online') }}
      @endif
      : {{ count($dataCustomers) }} Customers
    </h5>

  </div>

  <div class="card-body">
    @if (count($dataCustomers) > 0)
    {{ $data->links() }}
    <table id="customersTable" cellspacing="0" width="100%" class="table  datatable-loading no-footer sortable searchable">
      <tr>
        <th width="40px">{{ __('#') }}</th>
        <th>{{ __('customer.table.name') }}</th>
        <th>{{ __('customer.table.address') }}</th>
        <th>{{ __('customer.table.server') }}</th>
        <th>{{ __('customer.table.state') }}</th>

        <th width="80px">{{ __('customer.table.action') }}</th>
      </tr>


      @foreach ($data as $key => $customer)
      <tr>
        <td>{{ ++$i }}</td>
        <td><a href="{{ route('customers.show', $customer->slug) }}">{{ $customer->name ?? 'no data' }}</a></td>
        <td>{{ $customer->address ?? 'no data' }}</td>
        <td>
          <span class="badge bg-danger-soft text-danger">
            <i class="fas fa-server"></i> {{ $customer->mikrotik->name ?? 'No Data'}}
          </span>
        </td>

        <td>
          @if ($customer->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
          <span class="badge text-white bg-success">
            <i class="fas fa-lock-open"></i> {{ __('pppoe.secret.enabled') }}
          </span>
          @endif
          @if ($customer->disabled === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
          <span class="badge text-white bg-danger">
            <i class="fas fa-lock"></i> {{ __('pppoe.secret.disabled') }}
          </span>
          @endif
        </td>


        <td class="items-center justify-content-end">
          @canany(['secret-disable', 'customer-list', 'customer-edit', 'secret-edit', 'customer-delete' ])
          <x-drop-down-action>
            @can('customer-list')
            <li>
              <a class="dropdown-item" href="{{ route('customers.show',$customer->slug) }}">
                <i class="far fa-eye me-2 text-success"></i>
                {{ __('customer.action.view') }}
              </a>
            </li>
            @endcan
            @can('customer-edit')
            <li>
              <a class="dropdown-item" href="{{ route('customers.edit',$customer->slug) }}">
                <i class="far fa-edit me-2 text-success"></i>
                {{ __('customer.action.edit') }}
              </a>
            </li>
            <li>
              @if ($customer->disabled === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
              <form method="POST" class="d-inline" action="{{ route('customers.enable', $customer->slug) }}">
                @csrf
                @method('put')
                <a class="dropdown-item" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                  <i class="fa fa-check me-2 text-success"></i> {{ __('pppoe.secret.enable') }}
                </a>
              </form>
              @elseif ($customer->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
              <form method="POST" class="d-inline" action="{{ route('customers.disable', $customer->slug) }}">
                @csrf
                @method('put')
                <a class="dropdown-item" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                  <i class="fa fa-times text-warning me-2"></i> {{ __('pppoe.secret.disable') }}
                </a>
              </form>
              @endif
            </li>
            @endcan
            @can('customer-delete')
            <li>
              <a class="dropdown-item" href="{{ route('customers.delete',$customer->slug) }}"><i class="fa fa-trash me-2 text-danger"></i>&nbsp{{ __('customer.action.delete') }}</a>
            </li>
            @endcan
          </x-drop-down-action>
          @endcanany
        </td>
      </tr>
      @endforeach
    </table>
    {{ $data->links() }}
    @else
    @if ($status === 'offline')
    {{ __('No Customer Offline') }}
    @elseif ($status === 'isolir')
    {{ __('No Customer Isolir') }}
    @else
    {{ __('No Customer Online') }}
    @endif

    @endif

  </div>
</x-layouts.cpanel.customers.app-layout>