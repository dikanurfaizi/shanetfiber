<x-layouts.cpanel.settings.app-layout title="Setting Package">

  <div class="card-header py-3">
    <h5 class="m-0 font-weight-bold text-primary">{{ __('package.header.export') }}</h5>

  </div>

  <div class="row">
    <div class="col-lg-12">
      <div class="card-body col-lg-8">


        <div class="row align-items-center">
          <div class="col-md-12 mb-3">
            {!! Form::open(array('route' => 'packages.export','method'=>'POST')) !!}
            <label for="server"><strong>{{ __('customer.label.server') }}</strong></label>

            {!! Form::select('servers', $mikrotiks, null, array('placeholder' => 'Select Server','class' => 'form-control', 'id' => 'server-dropdown')) !!}

            @if ($errors->has('servers'))
            <span class="text-danger">{{ $errors->first('servers') }}</span>
            @endif
          </div>
        </div>
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-success"><i class="fas fa-arrow-alt-circle-right"></i> &nbsp {{ __('customer.button.export') }}</button>
      </div>
      {!! Form::close() !!}
    </div>
  </div>
</x-layouts.cpanel.settings.app-layout>