<x-layouts.app-layout title="WhatsApp Conversations">

    <div x-data="waApp()" x-init="init()" class="wa-container d-flex flex-column flex-md-row"
        style="height: 90vh; border: 1px solid #ccc; border-radius: 8px; overflow: hidden;">

        {{-- ================= SIDEBAR ================= --}}
        <div x-show="isListVisible" class="wa-sidebar p-2 flex-shrink-0"
            style="width:100%; max-width:300px; background:#f0f0f0; overflow-y:auto;">

            <input type="text" class="form-control mb-2" placeholder="Search a chat" x-model="search">

            <ul class="list-group">
                <template x-for="conv in filteredConversations" :key="conv.id">
                    <li class="list-group-item wa-contact" :class="{ 'active': selectedConversation?.id === conv.id }"
                        @click="selectConversation(conv)" style="cursor:pointer;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span x-text="conv.customer_name"></span>
                            <small class="text-muted" x-text="formatTime(conv.last_message_at)"></small>
                        </div>

                        <span x-show="conv.has_unread" class="badge bg-danger mt-1">
                            Unread
                        </span>
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

                        <template x-for="msg in sortedMessages" :key="msg.id">
                            <div class="mb-2 d-flex"
                                :class="{
                                    'justify-content-start': msg.direction === 'incoming',
                                    'justify-content-end': msg.direction === 'outgoing'
                                }">

                                <div class="p-2 rounded message-bubble" :class="msg.direction" style="max-width:65%;">

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

                                    {{-- META --}}
                                    <div class="message-meta">
                                        <span x-text="formatTime(msg.timestamp_wa)"></span>

                                        <template x-if="msg.direction === 'outgoing'">
                                            <span class="ms-1" :class="statusClass(msg.status)">
                                                <template x-if="msg.status==='sent'">✓</template>
                                                <template x-if="msg.status==='delivered'">✓✓</template>
                                                <template x-if="msg.status==='read'">✓✓</template>
                                            </span>
                                        </template>
                                    </div>

                                </div>
                            </div>
                        </template>

                    </div>

                    {{-- ================= INPUT ================= --}}
                    <div class="p-3 border-top bg-white">
                        <form class="d-flex" @submit.prevent="sendMessage">
                            <input type="text" class="form-control me-2" placeholder="Type a message..."
                                x-model="newMessage">
                            <button class="btn btn-success" :disabled="!newMessage.trim()">
                                Send
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
            .wa-contact.active {
                background: rgba(0, 0, 0, 0.05);
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
        </style>
    @endsection


    {{-- ================= SCRIPT ================= --}}
    @push('scripts')
        <script>
            function waApp() {
                return {

                    conversations: @json($conversationsJson),

                    search: '',
                    selectedConversation: null,
                    newMessage: '',
                    mobileView: window.innerWidth < 768 ? 'list' : null,

                    /* ================= INIT ================= */
                    init() {
                        window.addEventListener('resize', () => {
                            if (window.innerWidth >= 768) {
                                this.mobileView = null;
                            } else if (!this.mobileView) {
                                this.mobileView = 'list';
                            }
                        });
                    },

                    /* ================= COMPUTED ================= */
                    get isListVisible() {
                        return this.mobileView === null || this.mobileView === 'list';
                    },

                    get isChatVisible() {
                        return this.mobileView === null || this.mobileView === 'chat';
                    },

                    get filteredConversations() {
                        if (!this.search.trim()) return this.conversations;

                        return this.conversations.filter(c =>
                            c.customer_name?.toLowerCase()
                            .includes(this.search.toLowerCase())
                        );
                    },

                    get sortedMessages() {
                        if (!this.selectedConversation) return [];

                        return [...this.selectedConversation.messages]
                            .sort((a, b) => a.timestamp_wa - b.timestamp_wa);
                    },

                    /* ================= METHODS ================= */
                    selectConversation(conv) {
                        this.selectedConversation = conv;
                        if (window.innerWidth < 768) this.mobileView = 'chat';
                        this.$nextTick(() => this.scrollToBottom());
                    },

                    showList() {
                        this.mobileView = 'list';
                    },

                    sendMessage() {
                        if (!this.newMessage.trim()) return;

                        this.selectedConversation.messages.push({
                            id: Date.now(),
                            text_body: this.newMessage,
                            direction: 'outgoing',
                            timestamp_wa: Math.floor(Date.now() / 1000),
                            status: 'sent',
                            media: []
                        });

                        this.newMessage = '';
                        this.$nextTick(() => this.scrollToBottom());
                    },

                    scrollToBottom() {
                        const el = this.$refs.chatContainer;
                        if (el) el.scrollTop = el.scrollHeight;
                    },

                    formatTime(ts) {
                        if (!ts) return '';
                        const d = new Date(ts * 1000);
                        return d.getHours().toString().padStart(2, '0') + ':' +
                            d.getMinutes().toString().padStart(2, '0');
                    },

                    isImage(file) {
                        return file.mime_type?.startsWith('image/');
                    },

                    statusClass(status) {
                        if (status === 'read') return 'text-primary';
                        return 'text-muted';
                    }

                }
            }
        </script>
    @endpush

</x-layouts.app-layout>
