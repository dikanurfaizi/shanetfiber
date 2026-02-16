<x-layouts.app-layout title="WhatsApp Conversations">

    <div x-data="waApp()" x-init="initSwipe()" class="wa-container d-flex flex-column flex-md-row"
        style="height: 90vh; border: 1px solid #ccc; border-radius: 8px; overflow: hidden;">

        {{-- SIDEBAR / LIST CHAT --}}
        <div x-show="mobileView === null || mobileView === 'list'" class="wa-sidebar p-2 flex-shrink-0"
            style="width: 100%; max-width: 300px; background: #f0f0f0; overflow-y: auto;">

            <input type="text" class="form-control mb-2" placeholder="Search a chat" x-model="search">

            <ul class="list-group">
                <template x-for="conv in filteredConversations()" :key="conv.id">
                    <li class="list-group-item wa-contact"
                        :class="{ 'active': selectedConversation && selectedConversation.id === conv.id }"
                        @click="selectConversation(conv)" style="cursor: pointer;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span x-text="conv.customer_name" class="fw-normal"></span>
                            <small class="text-muted" x-text="formatTime(conv.last_message_at)"></small>
                        </div>
                        <span x-show="conv.has_unread" class="badge bg-danger mt-1">Unread</span>
                    </li>
                </template>
            </ul>
        </div>

        {{-- CHAT PANEL --}}
        <div x-show="mobileView === null || mobileView === 'chat'"
            class="wa-chat-panel flex-grow-1 p-3 d-flex flex-column position-relative"
            style="background: #e5ddd5; overflow-y: hidden; touch-action: pan-y;" @touchstart="touchStart($event)"
            @touchmove="touchMove($event)" @touchend="touchEnd($event)">

            {{-- Back button mobile --}}
            <div class="d-flex mb-2 d-md-none">
                <button class="btn btn-light" @click="mobileView='list'">← Back</button>
                <div class="ms-2 fw-bold" x-text="selectedConversation?.customer_name"></div>
            </div>

            {{-- Chat messages --}}
            <template x-if="selectedConversation">
                <div class="flex-grow-1 d-flex flex-column">
                    <div class="wa-chat-messages flex-grow-1 overflow-auto mb-2" x-ref="chatMessages">
                        <template
                            x-for="msg in selectedConversation.messages.sort((a,b)=>a.timestamp_wa - b.timestamp_wa)"
                            :key="msg.id">
                            <div class="mb-2 d-flex"
                                :class="{ 'justify-content-start': msg.direction === 'incoming', 'justify-content-end': msg
                                        .direction === 'outgoing' }">

                                <div class="p-2 rounded message-bubble"
                                    :class="{ 'incoming': msg.direction === 'incoming', 'outgoing': msg
                                        .direction === 'outgoing' }"
                                    style="max-width: 60%; position: relative;">

                                    <div x-text="msg.text_body" class="message-text"></div>

                                    <div class="message-meta">
                                        <span x-text="formatTime(msg.timestamp_wa)"></span>

                                        <template x-if="msg.direction==='outgoing'">
                                            <span class="message-status ms-1"
                                                :class="{
                                                    'sent': msg.status==='sent',
                                                    'delivered': msg.status==='delivered',
                                                    'read': msg.status==='read'
                                                }">
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

                    {{-- Input message --}}
                    <form class="wa-chat-input d-flex mt-auto" @submit.prevent="sendMessage">
                        <input type="text" class="form-control me-2" placeholder="Type a message..."
                            x-model="newMessage">
                        <button class="btn btn-success" :disabled="!newMessage.trim()">Send</button>
                    </form>
                </div>
            </template>

            {{-- Placeholder --}}
            <template x-if="!selectedConversation">
                <div class="text-center text-muted mt-5">
                    Select a conversation to preview messages.
                </div>
            </template>

        </div>
    </div>

    @section('custom_styles')
        <style>
            /* Sidebar */
            .wa-contact.active {
                background: rgba(0, 0, 0, 0.05);
                color: black;
                font-weight: normal;
            }

            /* Scrollbar */
            .wa-chat-messages {
                scrollbar-width: thin;
            }

            .wa-chat-messages::-webkit-scrollbar {
                width: 6px;
            }

            .wa-chat-messages::-webkit-scrollbar-thumb {
                background-color: rgba(0, 0, 0, .2);
                border-radius: 3px;
            }

            /* Chat bubbles */
            .message-bubble.incoming {
                background: white;
                color: black;
                border-radius: 8px;
                position: relative;
            }

            .message-bubble.outgoing {
                background: rgba(0, 128, 0, 0.15);
                color: black;
                border-radius: 8px;
                position: relative;
            }

            /* Meta (time + status) */
            .message-meta {
                display: flex;
                justify-content: flex-end;
                align-items: center;
                font-size: 10px;
                color: #888;
                margin-top: 4px;
            }

            .message-bubble.outgoing .message-meta {
                color: #555;
            }

            /* Centang status */
            .message-status.sent {
                color: gray;
            }

            .message-status.delivered {
                color: gray;
            }

            .message-status.read {
                color: #0b93f6;
            }
        </style>
    @endsection

    @push('scripts')
        <script>
            function waApp() {
                return {
                    search: '',
                    selectedConversation: null,
                    newMessage: '',
                    mobileView: window.innerWidth < 768 ? 'list' : null,
                    startX: 0,
                    currentX: 0,
                    swipeThreshold: 50, // px to trigger swipe

                    conversations: @json($conversationsJson),

                    selectConversation(conv) {
                        this.selectedConversation = conv;
                        if (window.innerWidth < 768) this.mobileView = 'chat';
                        this.$nextTick(() => this.scrollToBottom());
                    },

                    filteredConversations() {
                        if (!this.search.trim()) return this.conversations;
                        return this.conversations.filter(c => c.customer_name.toLowerCase().includes(this.search
                    .toLowerCase()));
                    },

                    formatTime(ts) {
                        if (!ts) return '';
                        const d = new Date(ts * 1000);
                        return d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0');
                    },

                    sendMessage() {
                        if (!this.newMessage.trim()) return;
                        this.selectedConversation.messages.push({
                            id: Date.now(),
                            text_body: this.newMessage,
                            direction: 'outgoing',
                            timestamp_wa: Math.floor(Date.now() / 1000),
                            status: 'sent'
                        });
                        this.newMessage = '';
                        this.$nextTick(() => this.scrollToBottom());
                    },

                    scrollToBottom() {
                        const container = this.$refs.chatMessages;
                        if (container) container.scrollTop = container.scrollHeight;
                    },

                    /* Swipe handlers */
                    touchStart(e) {
                        this.startX = e.touches[0].clientX;
                        this.currentX = this.startX;
                    },
                    touchMove(e) {
                        this.currentX = e.touches[0].clientX;
                    },
                    touchEnd(e) {
                        if (this.startX - this.currentX > this.swipeThreshold) {
                            // swipe left (do nothing for now)
                        } else if (this.currentX - this.startX > this.swipeThreshold) {
                            // swipe right -> back to list
                            if (this.mobileView === 'chat') this.mobileView = 'list';
                        }
                        this.startX = this.currentX = 0;
                    },

                    initSwipe() {
                        window.addEventListener('resize', () => {
                            if (window.innerWidth >= 768) this.mobileView = null;
                            else if (this.mobileView === null) this.mobileView = 'list';
                        });
                    }
                }
            }
        </script>
    @endpush

</x-layouts.app-layout>
