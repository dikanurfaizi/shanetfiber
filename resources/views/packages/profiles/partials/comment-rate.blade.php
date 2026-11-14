<div class="mb-3 row">
    <div class="col-sm-6">
        <label for="comment"><strong>{{ __('Comment') }}</strong></label>
        <input type="text" name="comment" id="comment" class="form-control"
            value="{{ old('comment', $pppProfile->comment ?? '') }}">
    </div>
    <div class="col-sm-6">
        <label for="rate-limit"><strong>{{ __('Rate Limit') }}</strong></label>
        <input type="text" name="rate_limit" id="rate-limit" class="form-control"
            value="{{ old('rate_limit', $pppProfile->rate_limit ?? '') }}" readonly>
    </div>
</div>
