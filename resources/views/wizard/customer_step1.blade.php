<x-layouts.cpanel.customers.app-layout :title="'Detail Customer'">

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Step 1: Customer Profile</h5>
        </div>

        <form method="POST" action="{{ route('wizard.step1') }}">
            @csrf

            <div class="card-body">

                {{-- Admin --}}
                <div class="form-group mb-3">
                    <label><strong>PIC / Responsibility</strong> <span class="text-danger">*</span></label>
                    <select name="user_id" class="form-control" required>
                        <option value="">-- Select PIC / Responsibility --</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Name --}}
                <div class="form-group mb-3">
                    <label><strong>Name</strong> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name"
                        value="{{ old('name', session('wizard.step1.name')) }}">
                    @error('name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="form-group mb-3">
                    <label><strong>Email</strong> <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email"
                        value="{{ old('email', session('wizard.step1.email')) }}">
                    @error('email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Contact --}}
                <div class="form-group mb-3">
                    <label><strong>Contact Number</strong> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="contact"
                        value="{{ old('contact', session('wizard.step1.contact')) }}">
                </div>

                {{-- Address --}}
                <div class="form-group mb-3">
                    <label><strong>Address</strong> (optional)</label>
                    <input type="text" class="form-control" name="address"
                        value="{{ old('address', session('wizard.step1.address')) }}">
                </div>

                {{-- Password --}}
                <div class="form-group mb-3">
                    <label><strong>Password</strong> (optional)</label>
                    <input type="password" class="form-control" name="password">
                    <small class="text-muted">Kosongkan jika ingin menggunakan default password.</small>
                    @error('password')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="form-group mb-3">
                    <label><strong>Confirm Password</strong></label>
                    <input type="password" class="form-control" name="password_confirmation">
                </div>

            </div>

            <div class="card-footer text-end">
                <button class="btn btn-primary">Next Step →</button>
            </div>

        </form>
    </div>

</x-layouts.cpanel.customers.app-layout>
