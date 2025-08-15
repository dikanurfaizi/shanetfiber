<x-layouts.cpanel.billings.app-layout title="{{ __('billing.title.create-one') }}">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('billing.header.create-one') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::open(['route' => 'billings.store.one', 'method' => 'POST']) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="periode"><strong>{{ __('billing.label.periode') }}</strong></label>
                        <div class="input-group">
                            <select name="id" class="form-control">
                                <option value="">{{ __('billing.placeholder.select.periode') }}</option>
                                @foreach ($periodes as $periode)
                                    <option value="{{ $periode->id }}">
                                        {{ $periode->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>


                <div class="card-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> &nbsp
                        {{ __('billing.button.save-add') }}</button>
                    <a class="btn btn-secondary" href="{{ route('billings.index') }}"><i class="fa fa-arrow-left"></i>
                        &nbsp {{ __('billing.button.cancel') }}</a>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
</x-layouts.cpanel.billings.app-layout>
