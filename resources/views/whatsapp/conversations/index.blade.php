<x-layouts.app-layout title="WhatsApp Conversations">

    <div x-data="waApp()" x-init="init()" class="wa-container d-flex flex-column flex-md-row"
        style="height: 90vh; border: 1px solid #ccc; border-radius: 8px; overflow: hidden;">

        {{-- ================= SIDEBAR ================= --}}
        <div x-show="isListVisible" class="wa-sidebar p-2 flex-shrink-0"
            style="width:100%; max-width:300px; background:#f0f0f0; overflow-y:auto;">

            <input type="text" class="form-control mb-2" placeholder="Search a chat" x-model="search">

            <ul class="list-group list-group-flush"> {{-- list-group-flush agar border samping hilang --}}
                <template x-for="conv in filteredConversations" :key="conv.id">
                    <li class="list-group-item wa-contact px-3 py-3"
                        :class="{ 'active': selectedConversation?.id === conv.id }" @click="selectConversation(conv)"
                        style="cursor:pointer;">

                        <div class="d-flex justify-content-between align-items-center">
                            {{-- Nama atau Nomor Customer --}}
                            <span x-text="conv.customer_name" style="font-size: 0.95rem;"></span>

                            {{-- Waktu Pesan Terakhir --}}
                            <small class="text-muted" x-text="formatTime(conv.last_message_at)"></small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-1">
                            {{-- Preview Pesan Terakhir (Opsional jika ada datanya) --}}
                            <small class="text-truncate text-muted" style="max-width: 180px;">
                                <span x-text="conv.last_message_body || ''"></span>
                            </small>

                            {{-- Badge Unread --}}
                            <span x-show="conv.has_unread" class="badge badge-success rounded-circle p-1"
                                style="background-color: #25d366; width: 20px; height: 20px;">
                                &nbsp;
                            </span>
                        </div>
                    </li>
                </template>
            </ul>
        </div>

        {{-- ================= CHAT PANEL ================= --}}
        <div x-show="isChatVisible" class="wa-chat-panel flex-grow-1 d-flex flex-column position-relative"
            style="background:#e5ddd5; min-height:0;">

            {{-- Mobile Back --}}
            <div class="d-flex p-3 d-md-none border-bottom bg-white">
                <button class="btn btn-light" @click="showList()">← Back</button>
                <div class="ms-2 fw-bold" x-text="selectedConversation?.customer_name"></div>
            </div>

            <template x-if="selectedConversation">
                <div class="flex-grow-1 d-flex flex-column" style="min-height:0;">

                    {{-- ================= MESSAGES ================= --}}
                    <div class="wa-chat-messages flex-grow-1 overflow-auto p-3" x-ref="chatContainer">

                        <template x-for="(msg, index) in sortedMessages" :key="msg.id">
                            <div>
                                {{-- DATE HEADER (Today, Yesterday, 12 April 2026) --}}
                                <template x-if="shouldShowDateHeader(sortedMessages, index)">
                                    <div class="d-flex justify-content-center my-3">
                                        <span class="badge shadow-sm px-3 py-2"
                                            style="background: #d1f4cc; color: #54656f; font-weight: 500; border-radius: 8px;"
                                            x-text="formatDateLabel(msg.timestamp_wa)">
                                        </span>
                                    </div>
                                </template>

                                {{-- BUBBLE CHAT --}}
                                <div class="mb-2 d-flex"
                                    :class="msg.direction === 'incoming' ? 'justify-content-start' : 'justify-content-end'">

                                    <div class="p-2 rounded message-bubble shadow-sm" :class="msg.direction"
                                        style="max-width:70%;">

                                        {{-- Tombol Reply Tetap Ada --}}
                                        <button type="button" class="reply-btn position-absolute"
                                            @click="setReply(msg)">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-reply mr-2"></i>
                                                <span style="font-size: 0.75rem; font-weight: 600;">Reply</span>
                                            </div>
                                        </button>

                                        {{-- TEXT --}}
                                        <template x-if="msg.text_body">
                                            <div class="message-text mb-1" x-text="msg.text_body"></div>
                                        </template>

                                        {{-- MEDIA --}}
                                        <template x-if="msg.media?.length">
                                            <div class="mt-2">

                                                <template x-for="file in msg.media" :key="file.id">
                                                    <div class="mb-2">

                                                        {{-- IMAGE --}}
                                                        <template x-if="isImage(file)">
                                                            <img :src="file.url" class="img-fluid rounded border"
                                                                style="max-height:250px; cursor:pointer;">
                                                        </template>

                                                        {{-- DOCUMENT --}}
                                                        <template x-if="!isImage(file)">
                                                            <a :href="file.url" target="_blank"
                                                                class="d-flex align-items-center p-2 border rounded bg-light text-decoration-none">
                                                                📎
                                                                <span class="ms-2 text-truncate"
                                                                    x-text="file.file_name"></span>
                                                            </a>
                                                        </template>

                                                    </div>
                                                </template>

                                            </div>
                                        </template>

                                        {{-- META (WAKTU & CENTANG) --}}
                                        <div class="message-meta d-flex align-items-center">
                                            <span x-text="formatTime(msg.timestamp_wa)"></span>

                                            <template x-if="msg.direction === 'outgoing'">
                                                <span class="ml-1" :class="statusClass(msg.status)"
                                                    style="font-size: 0.8rem;">
                                                    {{-- Centang 1 --}}
                                                    <template x-if="msg.status === 'sent'">
                                                        <i class="fas fa-check"></i>
                                                    </template>
                                                    {{-- Centang 2 Abu/Biru --}}
                                                    <template
                                                        x-if="msg.status === 'delivered' || msg.status === 'read'">
                                                        <i class="fas fa-check-double"></i>
                                                    </template>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- ================= INPUT ================= --}}
                    <div class="p-3 border-top bg-white">

                        {{-- Quote Preview --}}
                        <template x-if="replyingTo">
                            <div
                                class="d-flex align-items-center p-2 mb-2 border-start border-success border-4 bg-light rounded shadow-sm">
                                <div class="flex-grow-1 text-truncate">
                                    <small class="text-success fw-bold"
                                        x-text="replyingTo.direction === 'incoming' ? selectedConversation.customer_name : 'You'"></small>
                                    <div class="text-muted small text-truncate" x-text="replyingTo.text_body"></div>
                                </div>
                                <button type="button" class="btn-close btn-sm ms-2" @click="cancelReply()"></button>
                            </div>
                        </template>

                        <form class="d-flex align-items-center p-2 bg-light" @submit.prevent="sendMessage">
                            {{-- Container Input --}}
                            <div class="flex-grow-1 mr-3"> {{-- mr-3 memberikan jarak horizontal yang lebih pas --}}
                                <input type="text" id="chatInput"
                                    class="form-control rounded-pill border-0 shadow-sm" placeholder="Type a message..."
                                    x-model="newMessage" style="height: 45px; padding: 0 20px; line-height: 45px;">
                            </div>

                            {{-- Tombol Kirim --}}
                            <button type="submit"
                                class="btn btn-whatsapp-send rounded-circle d-flex align-items-center justify-content-center"
                                :disabled="!newMessage.trim() || isSending"
                                style="width: 45px; height: 45px; min-width: 45px; padding: 0;">

                                {{-- Ikon Pesawat Kertas --}}
                                <span x-show="!isSending" class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-paper-plane" style="font-size: 1.2rem;"></i>
                                </span>

                                {{-- Spinner saat loading --}}
                                <span x-show="isSending" class="spinner-border spinner-border-sm"
                                    role="status"></span>
                            </button>
                        </form>
                    </div>

                </div>
            </template>

            <template x-if="!selectedConversation">
                <div class="d-flex flex-grow-1 align-items-center justify-content-center text-muted">
                    Select a conversation to preview messages.
                </div>
            </template>

        </div>
    </div>

    {{-- ================= STYLES ================= --}}
    @section('custom_styles')
        <style>
            /* 1. Keadaan Normal (Bukan Aktif) */
            .wa-contact {
                transition: background-color 0.2s ease;
                border-left: 4px solid transparent !important;
                /* Standby untuk indikator hijau */
                color: #111b21 !important;
            }

            /* 2. Efek Hover (Saat kursor di atas tapi belum diklik) */
            .wa-contact:hover {
                background-color: #f5f6f6 !important;
                /* Abu-abu sangat muda */
                text-decoration: none;
            }

            /* 3. Efek Aktif (Saat chat dipilih) */
            .wa-contact.active {
                background-color: #ebebeb !important;
                /* Warna sedikit lebih gelap dari hover */
                color: #111b21 !important;
                border-left-color: #00a884 !important;
                /* Indikator hijau di samping */
            }

            /* 4. Menebalkan Nama/Nomor saat Aktif */
            .wa-contact.active span {
                font-weight: 800 !important;
                color: #000000 !important;
            }

            /* 5. Memastikan jam/waktu tidak berubah warna jadi putih */
            .wa-contact.active .text-muted,
            .wa-contact:hover .text-muted {
                color: #667781 !important;
            }

            .message-bubble.incoming {
                background: white;
            }

            .message-bubble.outgoing {
                background: rgba(0, 128, 0, 0.15);
            }

            .message-meta {
                display: flex;
                justify-content: flex-end;
                font-size: 10px;
                margin-top: 4px;
                color: #777;
            }

            .message-bubble {
                position: relative;
            }

            .reply-btn {
                opacity: 0;
                transition: opacity 0.2s;
                background: rgba(255, 255, 255, 0.8);
                border-radius: 50%;
                width: 25px;
                height: 25px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .message-bubble:hover .reply-btn {
                opacity: 1;
            }

            /* Agar tombol reply tidak keluar layar pada pesan outgoing */
            .outgoing .reply-btn {
                left: -35px;
                right: auto;
            }

            .incoming .reply-btn {
                right: -35px;
                left: auto;
            }

            .btn-close {
                box-sizing: content-box;
                width: 0.5em;
                height: 0.5em;
                padding: 0.25em 0.25em;
                color: #000;
                background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/0.5em auto no-repeat;
                border: 0;
                border-radius: 0.25rem;
                opacity: 0.5;
            }

            .btn-close:hover {
                opacity: 0.75;
            }

            .btn-whatsapp-send {
                background-color: #00a884;
                /* Hijau WhatsApp Modern */
                border: none;
                color: white;
                transition: all 0.2s ease;
                padding: 0;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            }

            .btn-whatsapp-send:hover {
                background-color: #008f6f;
                color: white;
                transform: scale(1.05);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            }

            .btn-whatsapp-send:active {
                transform: scale(0.95);
            }

            .btn-whatsapp-send:disabled {
                background-color: #aebac1;
                /* Warna abu-abu saat disabled */
                opacity: 0.8;
            }

            /* Memperbaiki posisi ikon agar benar-benar di tengah */
            .btn-whatsapp-send i {
                margin-left: -2px;
                /* Geser sedikit agar center secara optik */
            }

            /* Gunakan selector yang lebih spesifik agar menang dari sisa-sisa CSS lama */
            .wa-chat-messages .message-bubble .reply-btn {
                opacity: 0;
                visibility: hidden;
                transition: all 0.2s ease-in-out;

                /* WARNA: Abu-abu WhatsApp */
                background-color: #3b4a54 !important;
                color: #ffffff !important;

                /* BENTUK: Kapsul */
                border-radius: 20px !important;
                width: auto !important;
                height: auto !important;
                padding: 6px 14px !important;

                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                z-index: 1000;
                white-space: nowrap;
                border: none !important;
                cursor: pointer;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            }

            /* Hover state */
            .wa-chat-messages .message-bubble:hover .reply-btn {
                opacity: 1 !important;
                visibility: visible !important;
            }

            /* POSISI PESAN MASUK (Tombol di Kanan Bubble) */
            .wa-chat-messages .incoming .reply-btn {
                position: absolute !important;
                left: 100% !important;
                right: auto !important;
                /* Paksa mematikan right: -35px lama */
                top: 50% !important;
                transform: translateY(-50%) !important;
                margin-left: 15px !important;
                /* JARAK: Spasi antara bubble dan icon */
            }

            /* POSISI PESAN KELUAR (Tombol di Kiri Bubble) */
            .wa-chat-messages .outgoing .reply-btn {
                position: absolute !important;
                right: 100% !important;
                left: auto !important;
                /* Paksa mematikan left: -35px lama */
                top: 50% !important;
                transform: translateY(-50%) !important;
                margin-right: 15px !important;
                /* JARAK: Spasi antara bubble dan icon */
            }

            /* Style Ikon */
            .wa-chat-messages .reply-btn i {
                font-size: 0.8rem !important;
                margin-right: 8px !important;
                /* Jarak antara ikon panah dan teks 'Reply' */
            }

            /* status*/
            /* Warna centang biru khas WA */
            .status-read {
                color: #34b7f1 !important;
            }

            .status-delivered,
            .status-sent {
                color: #8696a0 !important;
            }
        </style>
    @endsection


    {{-- ================= SCRIPT ================= --}}
    @push('scripts')
        <script>
            window.waApp = function() {
                return {
                    conversations: @json($conversationsJson),
                    search: '',
                    selectedConversation: null,
                    newMessage: '',
                    isSending: false,
                    replyingTo: null,
                    mobileView: window.innerWidth < 768 ? 'list' : null,

                    init() {
                        window.addEventListener('resize', () => {
                            if (window.innerWidth >= 768) {
                                this.mobileView = null;
                            } else if (!this.mobileView) {
                                this.mobileView = 'list';
                            }
                        });
                    },

                    get isListVisible() {
                        return this.mobileView === null || this.mobileView === 'list';
                    },

                    get isChatVisible() {
                        return this.mobileView === null || this.mobileView === 'chat';
                    },

                    get filteredConversations() {
                        if (!this.search || !this.search.trim()) return this.conversations;
                        return this.conversations.filter(c =>
                            c.customer_name?.toLowerCase().includes(this.search.toLowerCase())
                        );
                    },

                    get sortedMessages() {
                        if (!this.selectedConversation || !this.selectedConversation.messages) return [];
                        return [...this.selectedConversation.messages]
                            .sort((a, b) => a.timestamp_wa - b.timestamp_wa);
                    },

                    selectConversation(conv) {
                        this.selectedConversation = conv;
                        if (window.innerWidth < 768) this.mobileView = 'chat';
                        this.$nextTick(() => this.scrollToBottom());
                    },

                    showList() {
                        this.mobileView = 'list';
                    },

                    sendMessage() {
                        if (!this.newMessage.trim() || this.isSending) return;
                        this.isSending = true;

                        const payload = {
                            conversation_id: this.selectedConversation.id,
                            message: this.newMessage,
                            reply_to_id: this.replyingTo ? this.replyingTo.wa_message_id : null
                        };

                        fetch("{{ route('whatsapp.conversations.send') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            })
                            .then(async response => {
                                const data = await response.json();
                                if (!response.ok) throw new Error(data.message || 'Error sending message');

                                this.selectedConversation.messages.push({
                                    id: Date.now(),
                                    wa_message_id: data.wa_id,
                                    text_body: this.newMessage,
                                    direction: 'outgoing',
                                    timestamp_wa: Math.floor(Date.now() / 1000),
                                    status: 'sent',
                                    media: []
                                });

                                this.newMessage = '';
                                this.replyingTo = null;
                                this.$nextTick(() => this.scrollToBottom());
                            })
                            .catch(error => alert(error.message))
                            .finally(() => this.isSending = false);
                    }, // <-- KOMA SUDAH DITAMBAHKAN

                    scrollToBottom() {
                        const el = this.$refs.chatContainer;
                        if (el) el.scrollTop = el.scrollHeight;
                    },

                    // Ganti fungsi formatTime lama dengan ini
                    formatTime(ts) {
                        if (!ts) return '';
                        const d = new Date(ts * 1000);
                        return d.getHours().toString().padStart(2, '0') + ':' +
                            d.getMinutes().toString().padStart(2, '0');
                    },

                    // Fungsi baru untuk Label Tanggal di Tengah Chat (UI WhatsApp)
                    formatDateLabel(ts) {
                        const date = new Date(ts * 1000);
                        const today = new Date();
                        const yesterday = new Date();
                        yesterday.setDate(today.getDate() - 1);

                        if (date.toDateString() === today.toDateString()) return 'Today';
                        if (date.toDateString() === yesterday.toDateString()) return 'Yesterday';

                        // Jika lebih dari kemarin, tampilkan tanggal lengkap
                        return date.toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric'
                        });
                    },

                    // Logic untuk mengecek apakah harus menampilkan header tanggal
                    shouldShowDateHeader(messages, index) {
                        if (index === 0) return true;
                        const currentDate = new Date(messages[index].timestamp_wa * 1000).toDateString();
                        const prevDate = new Date(messages[index - 1].timestamp_wa * 1000).toDateString();
                        return currentDate !== prevDate;
                    },

                    // Update statusClass untuk centang biru
                    statusClass(status) {
                        if (status === 'read') return 'status-read';
                        return 'status-delivered';
                    },

                    isImage(file) {
                        return file.mime_type?.startsWith('image/');
                    },

                    statusClass(status) {
                        if (status === 'read') return 'text-primary';
                        return 'text-muted';
                    },

                    setReply(msg) {
                        this.replyingTo = msg;
                        this.$nextTick(() => {
                            const input = document.getElementById('chatInput');
                            if (input) input.focus();
                        });
                    },

                    cancelReply() {
                        this.replyingTo = null;
                    }
                };
            }
        </script>
    @endpush

</x-layouts.app-layout>
