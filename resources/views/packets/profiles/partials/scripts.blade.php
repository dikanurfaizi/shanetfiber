<div class="form-group row">
    <div class="col-sm-6">
        <label for="on_up"><strong>{{ __('Script On Up') }}</strong></label>
        {!! Form::text('on_up', ':log warning "PPPOE $user login kembali.";', ['class' => 'form-control']) !!}
    </div>
    <div class="col-sm-6">
        <label for="on_down"><strong>{{ __('Script On Down') }}</strong></label>
        {!! Form::text('on_down', ':local profile [/ppp secret get [find name=$user] profile];', [
            'class' => 'form-control',
        ]) !!}
    </div>
</div>
