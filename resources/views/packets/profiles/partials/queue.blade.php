<div class="row mb-1">
    <div class="col-12">
        <label class="text-primary"><strong>{{ __('Queue') }}</strong></label>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="parent_queue"><strong>{{ __('paket.profile.label.parent_queue') }}</strong></label>
        <select id="parent-queue-dropdown" name="parent_queue" class="form-control">
            <option value="none"
                {{ (isset($pppProfile) && $pppProfile->parent_queue == 'none') || old('parent_queue') == 'none' ? 'selected' : '' }}>
                None</option>
            <!-- Tambahkan opsi lainnya jika perlu -->
            <option value="some_queue"
                {{ (isset($pppProfile) && $pppProfile->parent_queue == 'some_queue') || old('parent_queue') == 'some_queue' ? 'selected' : '' }}>
                Some Queue</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="insert_before"><strong>{{ __('paket.profile.label.insert_before') }}</strong></label>
        <select id="insert-before-dropdown" name="insert_before" class="form-control">
            <option value="default"
                {{ (isset($pppProfile) && $pppProfile->insert_before == 'default') || old('insert_before') == 'default' ? 'selected' : '' }}>
                Default</option>
            <option value="bottom"
                {{ (isset($pppProfile) && $pppProfile->insert_before == 'bottom') || old('insert_before') == 'bottom' ? 'selected' : '' }}>
                Bottom</option>
            <option value="first"
                {{ (isset($pppProfile) && $pppProfile->insert_before == 'first') || old('insert_before') == 'first' ? 'selected' : '' }}>
                First</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="queue_type"><strong>{{ __('paket.profile.label.queue_type') }}</strong></label>
        <select id="queue-type-dropdown" name="queue_type" class="form-control">
            <option value="none"
                {{ (isset($pppProfile) && $pppProfile->queue_type == 'none') || old('queue_type') == 'none' ? 'selected' : '' }}>
                None</option>
            <!-- Tambahkan opsi lainnya jika perlu -->
            <option value="simple_queue"
                {{ (isset($pppProfile) && $pppProfile->queue_type == 'simple_queue') || old('queue_type') == 'simple_queue' ? 'selected' : '' }}>
                Simple Queue</option>
        </select>
    </div>
</div>
