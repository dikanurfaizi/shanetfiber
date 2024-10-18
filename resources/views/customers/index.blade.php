<x-layouts.cpanel.customers.app-layout title="Customers">
  <div class="card-header py-3">
    <h5 class="m-0 font-weight-bold text-primary mb-2">
      {{ __('customer.header.index') }}
    </h5>

    <span>
      @if(isset($message))
      <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {{ $message }}
      </div>
      @else
      <div class="alert alert-success">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {{ $data->total() }} customers found
      </div>
      @endif
    </span>
  </div>

  <div class="card-body">
    {{ $data->withQueryString()->links() }}
    <table id="customersTable" cellspacing="0" width="100%" class="table table-bordered table-striped table-hover">
      <tr class="table-secondary text-center">
        <th>
          <span class="badge bg-danger-soft text-primary">
            {{ __('customer.table.no') }}
          </span>
        </th>
        <th>
          <span class="badge bg-danger-soft text-primary">
            {{ __('customer.table.name') }}
          </span>
        </th>
        <th>
          <span class="badge bg-danger-soft text-primary">
            {{ __('customer.table.address') }}
          </span>
        </th>
        <th>
          <span class="badge bg-danger-soft text-primary">
            {{ __('customer.table.activation_date') }}
          </span>
        </th>
        <th>
          <span class="badge bg-danger-soft text-primary">
            {{ __('customer.table.paket') }}
          </span>
        </th>
        <th>
          <span class="badge bg-danger-soft text-primary">
            {{ __('customer.table.bill') }}
          </span>
        </th>
        <th>
          <span class="badge bg-danger-soft text-primary">
            {{ __('customer.table.server') }}
          </span>
        </th>
        <th>
          <span class="badge bg-danger-soft text-primary">
            {{ __('customer.table.state') }}
          </span>
        </th>
        <th width="80px">
          <span class="badge bg-danger-soft text-primary">
            {{ __('customer.table.action') }}
          </span>
        </th>
      </tr>
      @foreach ($data as $key => $customer)
      <tr class="@if ($customer->disabled === 'true') text-secondary @else text-primary @endif">
        <td>
          <span class="badge bg-danger-soft">
            {{ ++$i }}
          </span>
        </td>
        <td>

          @can('customer-list')
          <a class="@if ($customer->disabled === 'true') text-secondary @else text-primary @endif" href="{{ route('customers.show',$customer->slug) }}">{{ $customer->name }}</a>
          @else
          {{ $customer->name }}
          @endcan

        </td>

        <td>
          <span class="badge bg-danger-soft">
            {{ $customer->address }}
        </td>
        <td>
          <span class="badge bg-danger-soft">
            @if ($customer->activation == 'true')
            {{ Carbon\Carbon::parse($customer->activation_date)->translatedFormat('d F Y') ?? 'No Data' }}
            @else
            <i class="text-danger">{{ __('Not Acitaved') }}</i>
            @endif
        </td>
        </span>
        <td>
          <span class="badge bg-danger-soft">
            <i class="fas fa-chalkboard"></i> {{ $customer->paket->name }}
          </span>
        </td>
        <td>
          <span class="badge bg-danger-soft">
            @moneyIDR($customer->paket->price * ((100-$customer->discount)/100))
          </span>
        </td>
        <td>
          <span class="badge bg-danger-soft @if ($customer->disabled === 'false') text-success @endif">
            <i class="fas fa-server"></i> {{ $customer->mikrotik->name }}
          </span>
        </td>

        <td>
          @if ($customer->activation == 'false')
          <span class="badge text-white bg-danger">
            <i class="fa fa-times text-warning me-2"></i> {{ __('customer.info.not-activation') }}
          </span>
          @else
          @if ($customer->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
          <span class="badge text-white @if ($customer->disabled === 'false') bg-success @endif">
            <i class="fas fa-lock-open"></i> {{ __('pppoe.secret.enabled') }}
          </span>
          @endif
          @if ($customer->disabled === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
          <span class="badge @if ($customer->disabled === 'false') bg-secondary @endif">
            <i class="fas fa-lock"></i> {{ __('pppoe.secret.disabled') }}
          </span>
          @endif
        </td>
        @endif
        <td class="items-center justify-content-end">
          @canany(['secret-disable', 'customer-edit', 'secret-edit', 'customer-delete' ])
          <x-drop-down-action>
            @can('customer-edit')
            <li>
              <a class="dropdown-item" href="{{ route('customers.edit',$customer->slug) }}">
                <i class="far fa-edit me-2 text-success"></i>
                {{ __('customer.action.edit') }}
              </a>
            </li>
            <li>
              @if ($customer->activation == 'false')
              <form method="POST" class="d-inline" action="{{ route('customers.activation',$customer->slug) }}">
                @csrf
                @method('put')
                <a class="dropdown-item" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                  <i class="fa fa-check me-2 text-success"></i>
                  {{ __('customer.action.activation') }}
                </a>
              </form>
              @else
              @if ($customer->disabled === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
              <form method="POST" class="d-inline" action="{{ route('customers.enable', $customer->slug) }}">
                @csrf
                @method('put')
                <a class="dropdown-item" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                  <i class="fa fa-check me-2 text-success"></i> {{ __('pppoe.secret.enable') }}
                </a>
              </form>
              <form method="POST" class="d-inline" action="{{ route('customers.forceEnable', $customer->slug) }}">
                @csrf
                @method('put')
                <a class="dropdown-item" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                  <i class="fa fa-check me-2 text-success"></i> {{ __('pppoe.secret.force-enable') }}
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
              <form method="POST" class="d-inline" action="{{ route('customers.forceDisable', $customer->slug) }}">
                @csrf
                @method('put')
                <a class="dropdown-item" href="#" onclick="event.preventDefault(); this.closest('form').submit();">
                  <i class="fa fa-times text-warning me-2"></i> {{ __('pppoe.secret.force-disable') }}
                </a>
              </form>
              @endif
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
    {{ $data->withQueryString()->links() }}
  </div>

</x-layouts.cpanel.customers.app-layout>