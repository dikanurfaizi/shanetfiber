<x-layouts.cpanel.billings.app-layout title="{{ __('billing.title.create') }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('billing.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::open(array('route' => 'billings.store','method'=>'POST')) !!}

            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="periode"><strong>{{ __('billing.label.periode') }}</strong></label>
                        <div class="input-group">
                            <select name="name" class="form-control">
                                <option value="">{{ __('billing.placeholder.select.periode') }}</option>

                                @foreach($billingPeriodes as $date)
                                <option value="{{ $date->format('F Y') }}" {{ $date->format('F Y') == request()->query('period') ? 'selected' : '' }}>
                                    {{ $date->format('F Y') }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success" id="button-save"><i class="fa fa-save"></i> {{ __('billing.button.save-create') }}</button>
                <a class="btn btn-primary" href="{{ route('billings.index') }}"><i class="fa fa-arrow-left"></i> {{ __('billing.button.cancel') }}</a>
            </div>
            {!! Form::close() !!}
            <div class="card-body">
                <table id="billingsTable" cellspacing="0" width="100%" class="table  datatable-loading no-footer sortable searchable">
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
                            <a href="{{ route('billing.periode.delete', $billingPeriode->id) }}" class="btn btn-danger btn-sm">
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
</x-layouts.cpanel.billings.app-layout>