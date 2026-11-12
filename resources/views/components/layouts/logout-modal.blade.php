<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <a href="{{ route('logout') }}" class="btn btn-primary"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        <i class="me-2 fas fa-sign-out-alt"></i>
                        {{ __('Log Out') }}
                    </a>
                </form>

            </div>
        </div>
    </div>
</div>
