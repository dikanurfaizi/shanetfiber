<div class="row mb-1">
    <div class="col-12">
        <label class="text-primary"><strong>{{ __('Queue') }}</strong></label>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="parent_queue"><strong>{{ __('paket.profile.label.parent_queue') }}</strong></label>
        <select id="parent-queue-dropdown" name="parent_queue" class="form-control">
            <option value="none">None</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="insert_before"><strong>{{ __('paket.profile.label.insert_before') }}</strong></label>
        <select id="insert-before-dropdown" name="insert_before" class="form-control">
            <option value="default">Default</option>
            <option value="bottom">Bottom</option>
            <option value="first">First</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="queue_type"><strong>{{ __('paket.profile.label.queue_type') }}</strong></label>
        <select id="queue-type-dropdown" name="queue_type" class="form-control">
            <option value="none">None</option>
        </select>
    </div>
</div>
