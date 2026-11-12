<x-layouts.cpanel.settings.app-layout title="{{ __('user.title.create') }}">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">{{ __('user.header.create') }}</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">

            {!! Form::open(['route' => 'users.store', 'method' => 'POST']) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-2">
                        <label for="name"><strong>{{ __('user.label.name') }}</strong></label>
                        <div class="input-group mb-1">
                            {!! Form::text('name', null, ['placeholder' => __('user.placeholder.name'), 'class' => 'form-control']) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-2">
                        <label for="email"><strong>{{ __('user.label.email') }}</strong></label>
                        <div class="input-group mb-1">
                            {!! Form::text('email', null, ['placeholder' => __('user.placeholder.email'), 'class' => 'form-control']) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('email'))
                            <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-2">
                        <label for="contact"><strong>{{ __('user.label.contact') }}</strong></label>
                        <div class="input-group mb-1">
                            <div class="input-group-prepend">
                                <div class="input-group-text">+62</div>
                            </div>
                            {!! Form::text('contact', null, [
                                'placeholder' => __('user.placeholder.contact'),
                                'class' => 'form-control',
                                'id' => 'contact',
                                'oninput' => 'validateContact()',
                                'title' => 'Nomor telepon harus dimulai dengan angka 8 dan hanya berisi angka',
                            ]) !!}
                        </div>
                        @if ($errors->has('contact'))
                            <span class="text-danger">{{ $errors->first('contact') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-2">
                        <label for="password"><strong>{{ __('user.label.password') }}</strong></label>
                        <div class="input-group mb-1">
                            {!! Form::password('password', ['placeholder' => __('user.placeholder.password'), 'class' => 'form-control']) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('password'))
                            <span class="text-danger">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-2">
                        <label for="confirm-password"><strong>{{ __('user.label.confirm-password') }}</strong></label>
                        <div class="input-group mb-1">
                            {!! Form::password('confirm-password', [
                                'placeholder' => __('user.placeholder.confirm-password'),
                                'class' => 'form-control',
                            ]) !!}
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('confirm-password'))
                            <span class="text-danger">{{ $errors->first('confirm-password') }}</span>
                        @endif
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-12 mb-2">
                        <label for="roles"><strong>{{ __('user.label.roles') }}</strong></label>
                        <div class="input-group">
                            {!! Form::select('roles[]', $roles, [], ['class' => 'form-control', 'multiple']) !!}
                        </div>
                        @if ($errors->has('roles'))
                            <span class="text-danger">{{ $errors->first('roles') }}</span>
                        @endif
                    </div>
                </div>

                {{-- === Toggle untuk akses data user lain === --}}
                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <div class="form-check form-switch ps-2">
                            <input class="form-check-input" type="checkbox" id="toggle-link-user"
                                name="can_view_related_users">
                            <label class="form-check-label fw-semibold" for="toggle-link-user">
                                Can see data managed by other users
                            </label>
                        </div>
                    </div>
                </div>

                {{-- === Container daftar user === --}}
                <div id="linked-users-container" class="row align-items-center" style="display: none;">
                    <div class="col-md-12 mb-3">
                        <label><strong>Select which users can be seen:</strong></label>

                        {{-- 🔄 Spinner Loading Indicator --}}
                        <div id="loading-spinner" class="text-center my-2" style="display: none;">
                            <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                                <span class="visually-hidden"></span>
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
                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-user-plus"></i> &nbsp
                    {{ __('user.button.save') }}</button>
                <a class="btn btn-sm btn-secondary" href="{{ route('users.index') }}"><i class="fa fa-arrow-left"></i>
                    &nbsp {{ __('user.button.back') }}</a>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
    @section('custom_styles')
        <style>
            .form-check.form-switch .form-check-input {
                margin-left: -1.25em !important;
                width: 2em !important;
                /* tetap jaga ukuran switch default */
            }

            .form-check.form-switch .form-check-label {
                margin-left: 1.5em !important;
                margin-top: 0.05em !important;
                /* beri ruang untuk switch */
            }

            #loading-spinner {
                min-height: 80px;
                /* jaga tinggi agar tidak melompat-lompat */
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

                // Awalnya sembunyikan container
                container.style.display = 'none';

                async function fetchLinkableUsers(selectedRole) {
                    if (!selectedRole) {
                        checkboxes.innerHTML = `<p class="text-danger mb-0">Select a role first.</p>`;
                        return;
                    }

                    const spinner = document.getElementById('loading-spinner');

                    container.style.display = 'block';
                    spinner.style.display = 'block';
                    checkboxes.style.display = 'none'; // 🔸 sembunyikan dulu kotak checkbox

                    try {
                        const response = await fetch("{{ route('user.linkable') }}" + "?role=" +
                            encodeURIComponent(selectedRole));
                        const data = await response.json();

                        spinner.style.display = 'none';
                        checkboxes.style.display = 'block'; // 🔹 tampilkan lagi setelah data siap
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
                        checkboxes.style.display = 'block'; // pastikan muncul lagi untuk pesan error
                        checkboxes.innerHTML = `<p class="text-danger mb-0">Failed to fetch users.</p>`;
                    }
                }

                // ketika toggle berubah
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

                        // ✅ tampilkan container lebih awal (agar spinner muncul segera)
                        container.style.display = 'block';
                        await fetchLinkableUsers(selectedRole);

                    } else {
                        container.style.display = 'none';
                    }
                });

                // ketika role berubah
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

                // Hanya izinkan angka dan pastikan dimulai dengan angka 8
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
