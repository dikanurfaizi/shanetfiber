<x-layouts.cpanel.billings.app-layout title="{{ __('billing.title.create') }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('billing.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card-body col-lg-8">
                <form method="POST" action="{{ route('billings.auto-generate') }}" id="auto_billing_form">
                    @csrf
                    <div class="row align-items-center mt-3">
                        <div class="col-md-6 mb-3">
                            <label
                                for="auto_billing_toggle"><strong>{{ __('billing.label.automatic') }}</strong></label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="auto_billing_toggle"
                                    name="billing_auto_generate" value="1"
                                    {{ $billingAutoGenerate ? 'checked' : '' }} onchange="handleToggleChanged();">
                                <label class="form-check-label" for="auto_billing_toggle">
                                    {{ __('billing.label.enable-automatic') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3" id="auto_billing_day_wrapper"
                            style="{{ $billingAutoGenerate ? '' : 'display:none;' }}">
                            <label
                                for="billing_auto_day"><strong>{{ __('billing.label.select-billing-date') }}</strong></label>
                            <select name="billing_auto_day" id="billing_auto_day" class="form-control"
                                onchange="submitAutoBillingForm();">
                                <option value="">{{ __('billing.label.select-date') }}</option>
                                @for ($i = 1; $i <= 28; $i++)
                                    <option value="{{ $i }}" {{ $billingAutoDay == $i ? 'selected' : '' }}>
                                        {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </form>

                {!! Form::open(['route' => 'billings.store', 'method' => 'POST']) !!}

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="periode"><strong>{{ __('billing.label.periode') }}</strong></label>
                        <div class="input-group">
                            <select name="name" class="form-control">
                                <option value="">{{ __('billing.placeholder.select.periode') }}</option>

                                @foreach ($billingPeriodes as $date)
                                    <option value="{{ $date->format('F Y') }}"
                                        {{ $date->format('F Y') == request()->query('period') ? 'selected' : '' }}>
                                        {{ $date->format('F Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success" id="button-save"><i class="fa fa-save"></i>
                    {{ __('billing.button.save-create') }}</button>
                <a class="btn btn-primary" href="{{ route('billings.index') }}"><i class="fa fa-arrow-left"></i>
                    {{ __('billing.button.cancel') }}</a>
            </div>
            {!! Form::close() !!}
            <div class="card-body">
                <table id="billingsTable" cellspacing="0" width="100%"
                    class="table  datatable-loading no-footer sortable searchable">
                    <tr>
                        <th width="80px">{{ __('billing.table.no') }}</th>
                        <th>{{ __('billing.table.periode') }}</th>
                        <th width="160px">{{ __('billing.table.action') }}</th>
                    </tr>
                    @foreach ($data as $key => $billingPeriode)
                        <tr>
                            <td>{{ ++$i }}</td>

                            <td>{{ $billingPeriode->name }}</td>

                            <td class="d-flex items-center justify-content-end">
                                <a href="{{ route('billing.periode.delete', $billingPeriode->id) }}"
                                    class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash me-2 text-white"></i>
                                    {{ __('billing.button.delete') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
    @section('custom_scripts')
        <script>
            function handleToggleChanged() {
                const toggle = document.getElementById('auto_billing_toggle');
                const wrapper = document.getElementById('auto_billing_day_wrapper');
                const form = document.getElementById('auto_billing_form');

                if (toggle.checked) {
                    wrapper.style.display = 'block';
                } else {
                    wrapper.style.display = 'none';
                    document.getElementById('billing_auto_day').value = '';
                    form.submit();
                }
            }

            function submitAutoBillingForm() {
                document.getElementById('auto_billing_form').submit();
            }
        </script>
    @endsection
</x-layouts.cpanel.billings.app-layout>
