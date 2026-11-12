<div class="mb-3 row">
    <div class="col-sm-6">
        <label for="on_up"><strong>{{ __('Script On Up') }}</strong></label>
        {!! Form::text('on_up', $pppProfile->on_up ?? null, [
            'class' => 'form-control',
            'placeholder' => ':log warning "PPPOE $user login kembali.";',
        ]) !!}
    </div>
    <div class="col-sm-6">
        <label for="on_down"><strong>{{ __('Script On Down') }}</strong></label>
        {!! Form::text('on_down', $pppProfile->on_down ?? null, [
            'class' => 'form-control',
            'placeholder' => ':local profile [/ppp secret get [find name=$user] profile];',
        ]) !!}
    </div>
</div>
