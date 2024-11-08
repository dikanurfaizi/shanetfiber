<x-layouts.cpanel.settings.app-layout title="Create WhatsApp Broadcast">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">Create WhatsApp Broadcast</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::open(['route' => 'whatsapp-gateway.store', 'method' => 'POST']) !!}
            <div class="card-body col-lg-8">

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="name"><strong>Name <span class="text-danger">*</span></strong></label>
                        <div class="input-group">
                            {!! Form::text('name', null, ['placeholder' => 'Input the Name', 'class' => 'form-control', 'required']) !!}
                        </div>
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label><strong>Auto Send</strong></label>
                        <div class="d-flex align-items-center">
                            <div class="form-switch me-3">
                                <input type="hidden" name="status" value="inactive"> <!-- Nilai default -->
                                <input class="form-check-input" value="active" type="checkbox" id="status"
                                    name="status" onchange="toggleBroadcastButton(this); updateLabel(this)" checked>
                                <label class="form-check-label" for="status" id="status-label">Active</label>
                            </div>
                            <div id="broadcast-button-container" style="display: none; margin-left:10px;">
                                <button type="button" class="btn btn-sm btn-primary">Send Broadcast WhatsApp
                                    Manual</button>
                            </div>
                        </div>
                        @if ($errors->has('status'))
                            <span class="text-danger">{{ $errors->first('status') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="description"><strong>Description <span class="text-danger">*</span></strong></label>
                        <div class="input-group">
                            {!! Form::textarea('description', null, [
                                'placeholder' => 'Input Description',
                                'class' => 'form-control',
                                'rows' => 3,
                                'required',
                            ]) !!}
                        </div>
                        @if ($errors->has('description'))
                            <span class="text-danger">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="schedule_date"><strong>WhatsApp Send Date (Numeric) <span
                                    class="text-danger">*</span></strong></label>
                        <div class="input-group">
                            {!! Form::number('schedule_date', null, [
                                'placeholder' => 'Input The Date',
                                'class' => 'form-control',
                                'required',
                                'min' => '1',
                                'max' => '31',
                                'id' => 'schedule_date',
                            ]) !!}
                        </div>
                        @if ($errors->has('schedule_date'))
                            <span class="text-danger">{{ $errors->first('schedule_date') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="schedule_time"><strong>WhatsApp Send Time (24 Hours Format) <span
                                    class="text-danger">*</span></strong></label>
                        <div class="input-group">
                            <div class="row w-100">
                                <div class="col-md-4 mb-3">
                                    <select class="form-control" name="schedule_hour" required>
                                        <option value="-1">Hours</option>
                                        @foreach (range(0, 23) as $h)
                                            <option value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <select class="form-control" name="schedule_minute" required>
                                        <option value="-1">Minutes</option>
                                        @foreach (range(0, 59) as $m)
                                            <option value="{{ $m }}">{{ sprintf('%02d', $m) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <select class="form-control" name="schedule_second" required>
                                        <option value="-1">Seconds</option>
                                        @foreach (range(0, 59) as $s)
                                            <option value="{{ $s }}">{{ sprintf('%02d', $s) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('schedule_time'))
                            <span class="text-danger">{{ $errors->first('schedule_time') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-12 mb-3">
                        <label for="message"><strong>Message <span class="text-danger">*</span></strong></label>
                        <div class="input-group">
                            {!! Form::textarea('message', null, [
                                'placeholder' => 'Input Message',
                                'class' => 'form-control',
                                'rows' => 5,
                                'style' => 'height: 300px;',
                                'required',
                            ]) !!}
                        </div>
                        @if ($errors->has('message'))
                            <span class="text-danger">{{ $errors->first('message') }}</span>
                        @endif
                    </div>
                </div>

                <div class="card-footer d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="fa fa-save"></i> &nbsp Save
                            </button>
                            <a class="btn btn-sm btn-secondary" href="{{ route('whatsapp-gateway.index') }}">
                                <i class="fa fa-arrow-left"></i>&nbsp Cancel
                            </a>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="input-group-text"
                                style="height: 30px; display: flex; align-items: center;">62</span>
                            <input type="text" class="form-control form-control-sm" id="phone-input"
                                placeholder="8xxxxxxxxxxx" pattern="[0-9]*" maxlength="15"
                                style="width: 180px; margin-right: 10px;"
                                onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                            <span id="length-indicator" class="text-muted" style="margin-right: 10px;">0/15</span>
                            <button type="button" class="btn btn-sm btn-info" id="test-button">
                                <i class="fa fa-paper-plane"></i>&nbsp Preview WhatsApp Message
                            </button>
                        </div>
                    </div>
                    <div id="error-message" class="text-danger mt-2" style="display: none;"></div>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>

    @section('custom_scripts')
        <script>
            // Fungsi untuk memvalidasi input apakah berada di antara 1-31
            function validateInput(value) {
                const parsedValue = parseInt(value, 10);
                return parsedValue >= 1 && parsedValue <= 31;
            }

            function setInputListeners() {
                const scheduleDateInput = document.getElementById('schedule_date');
                scheduleDateInput.addEventListener('input', function(e) {
                    if (!validateInput(e.target.value)) e.target.value = ''; // Kosongkan jika di luar batas
                });

                scheduleDateInput.addEventListener('keydown', function(e) {
                    const key = e.key;
                    if (!isValidKey(key, e.target.value)) {
                        e.preventDefault(); // Cegah input jika tidak valid
                    }
                });
            }

            function isValidKey(key, currentValue) {
                return (key >= '0' && key <= '9') || key === 'Backspace' || key === 'ArrowLeft' || key === 'ArrowRight' &&
                    (key === 'Backspace' || validateInput(currentValue + key));
            }

            function updateLabel(checkbox) {
                document.getElementById('status-label').textContent = checkbox.checked ? 'Active' : 'Inactive';
            }

            function setupPhoneInput() {
                const phoneInput = document.getElementById('phone-input');
                const lengthIndicator = document.getElementById('length-indicator');

                phoneInput.addEventListener('input', () => {
                    lengthIndicator.innerText = `${phoneInput.value.length}/15`;
                });
            }

            function setupButton() {
                const button = document.getElementById("test-button");
                if (!button) {
                    showError('There is an error. Please check about elemen button.');
                    return;
                }

                button.addEventListener("click", async () => {
                    const phoneInput = document.getElementById("phone-input").value;
                    const messageInput = document.querySelector('textarea[name="message"]').value;

                    const errorMessageDiv = document.getElementById("error-message");
                    errorMessageDiv.style.display = "none"; // Menghapus pesan kesalahan sebelumnya
                    errorMessageDiv.innerHTML = "";

                    // Validasi input tidak boleh kosong
                    if (!phoneInput || !messageInput) {
                        showError("Nomor telepon dan pesan tidak boleh kosong.");
                        return; // Hentikan proses jika input kosong
                    }

                    // Validasi dan format nomor telepon
                    const cleanedPhoneInput = phoneInput.replace(/\D/g, '');
                    if (!/^8\d{7,}$/.test(cleanedPhoneInput)) {
                        showError("Nomor tidak valid. Harus diawali dengan 8 dan memiliki minimal 8 digit.");
                        return; // Hentikan proses jika input tidak valid
                    }

                    const whatsappNumber = `${cleanedPhoneInput.replace(/^(08|8)/, '628')}@s.whatsapp.net`;
                    await sendMessage(whatsappNumber, messageInput);
                });
            }

            function showError(message) {
                const errorMessageDiv = document.getElementById("error-message");
                errorMessageDiv.innerHTML = message;
                errorMessageDiv.style.display = "block";
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonText: 'OK'
                });
            }

            async function sendMessage(number, message) {
                try {
                    const response = await fetch('/api/send-whatsapp', { // Mengirim ke endpoint Laravel
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content') // Pastikan CSRF token dikirim
                        },
                        body: JSON.stringify({
                            phone: number,
                            message: message
                        })
                    });

                    const data = await response.json();

                    Swal.fire({
                        icon: data.message === 'Successfully sent message' ? 'success' : 'error',
                        title: data.message === 'Successfully sent message' ? 'Message Sent!' : 'Message Failed!',
                        text: data.message === 'Successfully sent message' ? 'Please check your WhatsApp' :
                            parseMessage(data),
                        confirmButtonText: 'OK'
                    });
                } catch (error) {
                    showError(error.message || 'An error occurred during sending.');
                }
            }

            function parseMessage(response) {
                const message = response.message || response;
                const match = message.match(/Phone (\d+)@s\.whatsapp\.net is not on whatsapp/);
                return match ? `Phone ${match[1]} is not on WhatsApp` : response.message; // Pesan default jika tidak cocok
            }

            function toggleBroadcastButton(checkbox) {
                document.getElementById('broadcast-button-container').style.display = checkbox.checked ? 'none' : 'block';
            }

            // Fungsi untuk menampilkan modal konfirmasi
            function confirmBroadcast() {
                Swal.fire({
                    title: 'Are you sure??',
                    text: 'You will send a WhatsApp broadcast message to all valid customers',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, continue',
                    cancelButtonText: 'No, cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika pengguna menekan tombol "Ya", lanjutkan ke fungsi sendBroadcastWhatsApp
                        sendBroadcastWhatsApp();
                    }
                });
            }

            aasync
            function sendBroadcastWhatsApp() {
                const message = document.querySelector('textarea[name="message"]').value;
                if (message.length === 0) {
                    Swal.fire({
                        title: 'Message is empty',
                        text: 'Please fill in the message first',
                        icon: 'info',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                try {
                    const response = await fetch('/api/send-broadcast', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            message
                        })
                    });

                    const result = await response.json();

                    Swal.fire({
                        title: result.status === 'success' ? 'Success!' : 'Error!',
                        text: result.message,
                        icon: result.status === 'success' ? 'success' : 'error',
                        confirmButtonText: 'OK'
                    });
                } catch (error) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to queue the message',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }

            // Event listener untuk tombol send broadcast
            const sendBroadcastButton = document.querySelector('#broadcast-button-container button');
            sendBroadcastButton.addEventListener('click', confirmBroadcast);

            document.addEventListener('DOMContentLoaded', () => {
                setInputListeners();
                setupPhoneInput();
                setupButton();
            });
        </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>
