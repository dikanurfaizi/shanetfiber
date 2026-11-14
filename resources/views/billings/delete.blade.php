<x-layouts.cpanel.billings.app-layout title="{{ __('billing.title.delete') }}">

    <div class="card-header py-3 alert-danger">
        <h5 class="m-0 fw-bold"> {{ __('billing.header.delete', ['billingperiode' => $billingperiode->name]) }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            <form action="{{ route('billing.periode.destroy', ['billingperiode' => $billingperiode->id]) }}" method="POST">
                @csrf
                @method('DELETE')
            <div class="card-body col-lg-10">
                <div class="col-md-12 mb-3">
                    <div class="alert alert-danger">
                        <strong>{{ __('billing.info.are_you_sure_to_delete_billing', ['billingperiode' => $billingperiode->name]) }}</strong><br><br>
                        {{ __('billing.info.warning-delete', ['billingperiode' => $billingperiode->name]) }}<br>
                        {{ __('billing.info.after-delete') }}<br><br>
                        {{ __('billing.info.press-button', ['button-delete' => 'delete', 'button-cancel' => 'cancel', 'billingperiode' => $billingperiode->name]) }}
                        <div class="mt-3">
                            @can('server-delete')
                                <button type="submit" class="btn btn-sm btn-danger"><i
                                        class="fa fa-trash me-2"></i>&nbsp{{ __('billing.button.delete') }}</button>
                            @endcan
                            <a class="btn btn-sm btn-secondary" href="{{ route('billings.create') }}"><i
                                    class="fa fa-arrow-left"></i>&nbsp{{ __('billing.button.cancel') }}</a>
                        </div>
                    </div>

                </div>
            </div>
            </form>
        </div>
</x-layouts.cpanel.billings.app-layout>
