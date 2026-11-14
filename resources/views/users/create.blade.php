<x-layouts.cpanel.settings.app-layout title="{{ __('user.title.create') }}">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('user.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="card-body col-lg-8">
                    {{-- Name --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="name"
                                class="form-label"><strong>{{ __('user.label.name') }}</strong></label>
                            <div class="input-group">
                                <input type="text" name="name" id="name" value="{{ old('name') }}"
                                    placeholder="{{ __('user.placeholder.name') }}"
                                    class="form-control @error('name') is-invalid @enderror">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="email"
                                class="form-label"><strong>{{ __('user.label.email') }}</strong></label>
                            <div class="input-group">
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                    placeholder="{{ __('user.placeholder.email') }}"
                                    class="form-control @error('email') is-invalid @enderror">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Contact --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="contact"
                                class="form-label"><strong>{{ __('user.label.contact') }}</strong></label>
                            <div class="input-group">
                                <span class="input-group-text">+62</span>
                                <input type="text" name="contact" id="contact" value="{{ old('contact') }}"
                                    placeholder="{{ __('user.placeholder.contact') }}"
                                    class="form-control @error('contact') is-invalid @enderror"
                                    oninput="validateContact()"
                                    title="Nomor telepon harus dimulai dengan angka 8 dan hanya berisi angka">
                                @error('contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="password"
                                class="form-label"><strong>{{ __('user.label.password') }}</strong></label>
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                    placeholder="{{ __('user.placeholder.password') }}"
                                    class="form-control @error('password') is-invalid @enderror">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Confirm Password --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="confirm-password"
                                class="form-label"><strong>{{ __('user.label.confirm-password') }}</strong></label>
                            <div class="input-group">
                                <input type="password" name="confirm-password" id="confirm-password"
                                    placeholder="{{ __('user.placeholder.confirm-password') }}"
                                    class="form-control @error('confirm-password') is-invalid @enderror">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                @error('confirm-password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Roles --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <label for="roles"
                                class="form-label"><strong>{{ __('user.label.roles') }}</strong></label>
                            <select name="roles[]" id="roles"
                                class="form-select @error('roles') is-invalid @enderror" multiple>
                                @foreach ($roles as $key => $value)
                                    <option value="{{ $key }}" @selected(in_array($key, (array) old('roles', [])))>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('roles')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- === Toggle untuk akses data user lain === --}}
                    <div class="row align-items-center">
                        <div class="col-md-12 mb-3">
                            <div class="form-check form-switch ps-2">
                                <input class="form-check-input" type="checkbox" id="toggle-link-user"
                                    name="can_view_related_users" @checked(old('can_view_related_users'))>
                                <label class="form-check-label fw-semibold" for="toggle-link-user">
                                    Can see data managed by other users
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- === Container daftar user === --}}
                    <div id="linked-users-container" class="row align-items-center" style="display: none;">
                        <div class="col-md-12 mb-3">
                            <label class="form-label"><strong>Select which users can be seen:</strong></label>

                            {{-- 🔄 Spinner Loading Indicator --}}
                            <div id="loading-spinner" class="text-center my-2" style="display: none;">
                                <div class="spinner-border text-primary" role="status"
                                    style="width: 2rem; height: 2rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted small mb-0">Loading users...</p>
                            </div>

                            {{-- 📋 Daftar Checkbox User --}}
                            <div id="linked-users-checkboxes" class="border p-2 rounded"
                                style="max-height: 200px; overflow-y: auto;">
                                <p class="text-muted m-0">There are no users to link.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fas fa-user-plus"></i>&nbsp;{{ __('user.button.save') }}
                    </button>
                    <a class="btn btn-sm btn-secondary" href="{{ route('users.index') }}">
                        <i class="fa fa-arrow-left"></i>&nbsp;{{ __('user.button.back') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    @section('custom_styles')
        <style>
            .form-check.form-switch .form-check-input {
                margin-left: -1.25em !important;
                width: 2em !important;
            }

            .form-check.form-switch .form-check-label {
                margin-left: 1.5em !important;
                margin-top: 0.05em !important;
            }

            #loading-spinner {
                min-height: 80px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }
        </style>
    @endsection

    @section('custom_scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const toggle = document.getElementById('toggle-link-user');
                const roleSelect = document.querySelector('select[name="roles[]"]');
                const container = document.getElementById('linked-users-container');
                const checkboxes = document.getElementById('linked-users-checkboxes');

                container.style.display = 'none';

                async function fetchLinkableUsers(selectedRole) {
                    if (!selectedRole) {
                        checkboxes.innerHTML = `<p class="text-danger mb-0">Select a role first.</p>`;
                        return;
                    }

                    const spinner = document.getElementById('loading-spinner');

                    container.style.display = 'block';
                    spinner.style.display = 'block';
                    checkboxes.style.display = 'none';

                    try {
                        const response = await fetch("{{ route('user.linkable') }}" + "?role=" +
                            encodeURIComponent(selectedRole));
                        const data = await response.json();

                        spinner.style.display = 'none';
                        checkboxes.style.display = 'block';
                        checkboxes.innerHTML = "";

                        if (!data.length) {
                            checkboxes.innerHTML = `<p class="text-muted mb-0">No users available to link.</p>`;
                            return;
                        }

                        data.forEach((user) => {
                            const wrapper = document.createElement("div");
                            wrapper.className = "form-check";
                            wrapper.innerHTML = `
                                <input class="form-check-input" type="checkbox" name="linked_user_ids[]" value="${user.id}" id="user_${user.id}">
                                <label class="form-check-label" for="user_${user.id}">
                                    ${user.name} <span class="text-muted small">(${user.roleName})</span>
                                </label>
                            `;
                            checkboxes.appendChild(wrapper);
                        });

                    } catch (error) {
                        console.error("Fetch error:", error);
                        spinner.style.display = 'none';
                        checkboxes.style.display = 'block';
                        checkboxes.innerHTML = `<p class="text-danger mb-0">Failed to fetch users.</p>`;
                    }
                }

                toggle.addEventListener("change", async function() {
                    checkboxes.innerHTML = "";

                    if (this.checked) {
                        const selectedRole = roleSelect.options[roleSelect.selectedIndex]?.value;

                        if (!selectedRole) {
                            container.style.display = 'block';
                            checkboxes.innerHTML = `
                                <p class="text-danger mb-0">
                                    Please select a role first before linking users.
                                </p>
                            `;
                            roleSelect.classList.add('border-danger');
                            setTimeout(() => roleSelect.classList.remove('border-danger'), 1500);
                            return;
                        }

                        container.style.display = 'block';
                        await fetchLinkableUsers(selectedRole);

                    } else {
                        container.style.display = 'none';
                    }
                });

                roleSelect.addEventListener("change", async function() {
                    const selectedRole = this.options[this.selectedIndex]?.value;
                    if (toggle.checked) {
                        await fetchLinkableUsers(selectedRole);
                    }
                });
            });

            function validateContact() {
                const contactField = document.getElementById('contact');
                const contactValue = contactField.value;

                contactField.value = contactValue.replace(/[^0-9]/g, '');

                if (!contactField.value.startsWith('8') || contactField.value.length < 8) {
                    contactField.style.borderColor = 'red';
                } else {
                    contactField.style.borderColor = '';
                }
            }
        </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>
