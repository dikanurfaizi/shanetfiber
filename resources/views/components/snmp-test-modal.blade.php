<!-- SNMP Test Result Modal -->
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-network-wired me-2"></i>
                    {{ $title }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Default content kalau slot kosong --}}
                <div id="snmp-test-result">
                    {{ $slot ?? 'Results will be inserted here' }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ $closeText }}
                </button>
            </div>
        </div>
    </div>
</div>
