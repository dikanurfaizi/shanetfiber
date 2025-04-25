<div class="form-group row">
    <div class="col-sm-6">
        <label for="comment"><strong>{{ __('Comment') }}</strong></label>
        {!! Form::text('comment', null, ['class' => 'form-control']) !!}
    </div>
    <div class="col-sm-6">
        <label for="rate_limit"><strong>{{ __('Rate Limit') }}</strong></label>
        {!! Form::text('rate_limit', null, ['class' => 'form-control', 'readonly' => true, 'id' => 'rate-limit']) !!}
    </div>
</div>
