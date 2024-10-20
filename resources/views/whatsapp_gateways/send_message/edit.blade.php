<x-layouts.cpanel.settings.app-layout title="Edit WhatsApp Broadcast">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">Edit WhatsApp Broadcast</h5>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {!! Form::model($whatsappGateway, [
                'method' => 'PATCH',
                'route' => ['whatsapp-gateway.update', $whatsappGateway->id],
            ]) !!}
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
                                <input class="form-check-input" @if ($whatsappGateway->status === 'active') checked @endif
                                    value="active" type="checkbox" id="status" name="status"
                                    onchange="toggleBroadcastButton(this); updateLabel(this)">
                                <label class="form-check-label" for="status" id="status-label">
                                    {{ $whatsappGateway->status === 'active' ? 'Active' : 'Inactive' }}
                                </label>
                            </div>
                            <div id="broadcast-button-container" style="display: none; margin-left:10px;">
                                <button type="button" class="btn btn-sm btn-primary">Send Broadcast WhatsApp
                                    Manual</button>
                            </div>
                            <!-- Tombol update di samping checkbox -->
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
                                'id' => 'schedule_date', // Tambahkan id untuk mempermudah akses JavaScript
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
                                <div class="col-md-4 mb-3"> <!-- Mengubah dari col-md-6 ke col-md-4 -->
                                    <label for="schedule_hour"><strong>Hours</strong></label>
                                    <select class="form-control" name="schedule_hour" required>
                                        <option value="">Hours</option>
                                        @foreach (range(0, 23) as $h)
                                            <option value="{{ sprintf('%02d', $h) }}"
                                                {{ sprintf('%02d', $h) == $hour ? 'selected' : '' }}>
                                                {{ sprintf('%02d', $h) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3"> <!-- Mengubah dari col-md-6 ke col-md-4 -->
                                    <label for="schedule_minute"><strong>Minutes</strong></label>
                                    <select class="form-control" name="schedule_minute" required>
                                        <option value="">Minutes</option>
                                        @foreach (range(0, 59) as $m)
                                            <option value="{{ sprintf('%02d', $m) }}"
                                                {{ sprintf('%02d', $m) == $minute ? 'selected' : '' }}>
                                                {{ sprintf('%02d', $m) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3"> <!-- Mengubah dari col-md-6 ke col-md-4 -->
                                    <label for="schedule_second"><strong>Seconds</strong></label>
                                    <select class="form-control" name="schedule_second" required>
                                        <option value="">Seconds</option>
                                        @foreach (range(0, 59) as $s)
                                            <option value="{{ sprintf('%02d', $s) }}"
                                                {{ sprintf('%02d', $s) == $second ? 'selected' : '' }}>
                                                {{ sprintf('%02d', $s) }}
                                            </option>
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
                        <div class="input-group" id="message-input">
                            {!! Form::textarea('message', null, [
                                'placeholder' => 'Input Message',
                                'class' => 'form-control',
                                'rows' => 5, // Ubah jumlah baris di sini
                                'style' => 'height: 300px;', // Atur tinggi textarea
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
                    <!-- Div untuk menampilkan pesan kesalahan -->
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
                scheduleDateInput.addEventListener('input', (e) => {
                    if (!validateInput(e.target.value)) e.target.value = ''; // Kosongkan jika di luar batas
                });

                scheduleDateInput.addEventListener('keydown', (e) => {
                    if (!isValidKey(e.key, e.target.value)) {
                        e.preventDefault(); // Cegah input jika tidak valid
                    }
                });
            }

            function isValidKey(key, currentValue) {
                return (key >= '0' && key <= '9') || key === 'Backspace' || key === 'ArrowLeft' || key === 'ArrowRight' && (
                    key === 'Backspace' || validateInput(currentValue + key));
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

            function validateInputName() {
                const nameInput = document.querySelector("input[name='name']");
                nameInput.addEventListener("input", function() {
                    this.value = this.value.replace(/[^a-zA-Z0-9\s]/g, ""); // Hanya alfanumerik dan spasi
                });
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

            async function sendBroadcastWhatsApp() {
                try {
                    // 1. Validasi pesan (message) apakah sudah diisi
                    const message = document.querySelector('textarea[name="message"]').value;
                    if (message.length === 0) {
                        Swal.fire({
                            title: 'Message is still empty',
                            text: 'Make sure the message has been filled in first',
                            icon: 'info',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    // 2. Ambil dan validasi data pelanggan
                    const response = await fetch('/api/get-customers');
                    if (!response.ok) throw new Error('Failed to retrieve customer data');
                    const customers = await response.json();

                    if (customers.length === 0) {
                        Swal.fire({
                            title: 'No Customers',
                            text: 'There are no registered customers',
                            icon: 'info',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    // Validasi kontak pelanggan
                    const validContacts = [];
                    const invalidContacts = [];

                    customers.forEach(customer => {
                        const contactNumber = customer.contact;
                        if (contactNumber && typeof contactNumber === 'string') {
                            if (!isNaN(contactNumber)) {
                                if (contactNumber.startsWith('08')) {
                                    validContacts.push(contactNumber.replace('0', '62'));
                                } else if (contactNumber.startsWith('8')) {
                                    validContacts.push('62' + contactNumber);
                                } else {
                                    invalidContacts.push(contactNumber);
                                }
                            } else {
                                invalidContacts.push(contactNumber);
                            }
                        } else {
                            invalidContacts.push(contactNumber || 'null/undefined');
                        }
                    });

                    // 3. Kirim nomor kontak yang valid dan pesan ke backend
                    if (validContacts.length > 0) {
                        const body = {
                            contacts: validContacts, // Array berisi nomor kontak yang valid
                            message: message
                        };

                        //console.log("Contacts being sent: ", validContacts); // Debugging kontak
                        //console.log("Message being sent: ", message); // Debugging pesan

                        try {
                            // Ambil CSRF token dari meta tag
                            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                            //console.log("CSRF Token: ", csrfToken); // Debugging CSRF token

                            const apiResponse = await fetch(
                                '/api/send-broadcast', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    body: JSON.stringify(body)
                                }
                            );
                            //console.log("API Response Status: ", apiResponse.status);

                            if (!apiResponse.ok) {
                                const errorData = await apiResponse.text(); // Ambil sebagai teks untuk debugging
                                //console.log("Error Response Text: ", errorData); // Debugging jika ada error dari server
                                throw new Error(errorData.message || 'Unknown error');
                            }

                            const jsonResponse = await apiResponse.json(); // Parsing JSON jika responsnya valid
                            //console.log("Parsed JSON Response: ", jsonResponse);

                            Swal.fire({
                                icon: 'success',
                                title: 'Successfully',
                                text: 'Sent WhatsApp message to all customers',
                                confirmButtonText: 'OK'
                            });
                        } catch (error) {
                            //console.log("Error caught: ", error); // Debugging error di blok catch
                            Swal.fire({
                                title: 'Error sending messages',
                                text: error.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    }

                    // Notifikasi untuk kontak yang tidak valid
                    if (invalidContacts.length > 0) {
                        Swal.fire({
                            title: 'Invalid Contacts',
                            text: 'The following contact numbers are not in the correct format: ' + invalidContacts
                                .join(', '),
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error',
                        text: error.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }

            // Event listener untuk tombol send broadcast
            const sendBroadcastButton = document.querySelector('#broadcast-button-container button');
            sendBroadcastButton.addEventListener('click', confirmBroadcast);

            document.querySelector('form').addEventListener('submit', function() {
                const checkbox = document.getElementById('status');
                this.status.value = checkbox.checked ? 'active' : 'inactive';
            });

            document.addEventListener('DOMContentLoaded', () => {
                setInputListeners();
                setupPhoneInput();
                setupButton();
                validateInputName();
                updateLabel(document.getElementById('status')); // Initialize label based on checkbox state
                toggleBroadcastButton(document.getElementById('status')); // Initialize button visibility
            });
        </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>
