<div class="mb-3 row">
    <div class="col-sm-6">
        <label for="on_up"><strong>{{ __('Script On Up') }}</strong></label>
        <input type="text" name="on_up" id="on_up" class="form-control"
            placeholder=':log warning "PPPOE $user login kembali.";' value="{{ old('on_up', $pppProfile->on_up ?? '') }}">
    </div>
    <div class="col-sm-6">
        <label for="on_down"><strong>{{ __('Script On Down') }}</strong></label>
        <input type="text" name="on_down" id="on_down" class="form-control"
            placeholder=":local profile [/ppp secret get [find name=$user] profile];"
            value="{{ old('on_down', $pppProfile->on_down ?? '') }}">
    </div>
</div>
