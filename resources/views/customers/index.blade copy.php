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
      <tr>
        <td>
          <span class="badge bg-danger-soft text-primary">
            {{ ++$i }}
          </span>
        </td>
        <td>
          <i id="status-online-{{$customer->slug}}" class="far fa-dot-circle text-success"></i>
          @can('customer-list')
          <a class="text-primary" href="{{ route('customers.show',$customer->slug) }}">{{ $customer->name }}</a>
          @else
          {{ $customer->name }}
          @endcan
          <div class="text-xs fw-bold text-success d-inline-flex align-items-center">
            {{ $customer->ppp_type->name ?? 'No Service'}}
          </div>
        </td>

        <td>
          <span class="badge bg-danger-soft text-primary">
            {{ $customer->address }}
        </td>
        </span>
        <td>
          <span class="badge bg-danger-soft text-primary">
            <i class="fas fa-chalkboard"></i> {{ $customer->paket->name }}
          </span>
        </td>
        <td>
          <span class="badge bg-danger-soft text-primary">
            @moneyIDR($customer->paket->price * ((100-$customer->discount)/100))
          </span>
        </td>
        <td>
          <span class="badge bg-danger-soft text-success">
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

  <!--  @section('custom_scripts')
  @foreach ($data as $key => $customer)
  <script>
    $(document).ready(function() {
      $("#status-online-{{$customer->slug}}").removeClass("text-success");
      $("#status-online-{{$customer->slug}}").addClass("text-danger");

      function checkStatusOnline() {
        const username = "{{$customer->username}}";
        const url = "{{route('get.ppp.active.byName', $customer->mikrotik)}}";
        $.ajax({
          url: url,
          method: "GET",
          data: {
            'username': username
          },
          success: function(response) {
            if (response.status === "online") {
              $("#status-online-{{$customer->slug}}").removeClass("text-danger");
              $("#status-online-{{$customer->slug}}").addClass("text-success");
            } else if (response.status === "offline") {
              $("#status-online-{{$customer->slug}}").removeClass("text-success");
              $("#status-online-{{$customer->slug}}").addClass("text-danger");
            }
          },
          error: function(xhr, status, error) {
            console.error(error);
            $("#test1").text(response.status);
          }
        });
      }
      setInterval(function() {
        checkStatusOnline();
      }, 10000);
    });
  </script>
  @endforeach
  @endsection
  -->
</x-layouts.cpanel.customers.app-layout>