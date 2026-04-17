<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="d-flex flex-column p-3 text-white bg-dark rounded">

                @if (!isset($chat))
                    <h3 class="text-center">Select a chat to view messages</h3>
                @else
                    {{-- HEADER --}}
                    <div class="col-12 mb-3">
                        <div class="p-3 rounded text-center text-white shadow"
                            style="background: linear-gradient(135deg, #0d6efd, #6610f2);">

                            <div class="d-flex justify-content-center align-items-center gap-2">
                                <span style="font-size: 1.4rem;">💬</span>

                                <a href="{{ route('order.show-order', $chat->order) }}"
                                    class="text-white text-decoration-none fw-bold" style="font-size: 1.25rem;">
                                    {{ $chat->order->title }}
                                </a>
                            </div>

                            <div class="mt-1 small opacity-75">
                                Click to view order details
                            </div>
                        </div>
                    </div>

                    {{-- CHAT BOX --}}
                    <div id="chat-box" style="height: 500px; overflow-y: auto;">
                        <div id="messages-container" data-first-id="{{ $messages->first()?->id }}"
                            data-last-id="{{ $messages->last()?->id }}">

                            @foreach ($messages as $message)
                                @include('components.pages.chats.message', ['message' => $message])
                            @endforeach

                        </div>
                    </div>

                    {{-- FORM --}}
                    <form id="chat-form" action="{{ route('chat.send-message', $chat) }}" method="POST" class="mt-4">
                        @csrf

                        <div class="d-flex align-items-end gap-2 p-2 bg-dark border border-secondary rounded">
                            <textarea name="message" class="form-control bg-dark text-white border-0 shadow-none" placeholder="Type a message..."
                                maxlength="500" required rows="1" style="resize:none;"></textarea>
                            <button type="submit" class="btn btn-primary px-4 py-2">Send</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>


@if (isset($chat))
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const chatBox = document.getElementById('chat-box');
            const container = document.getElementById('messages-container');
            const form = document.getElementById('chat-form');

            if (!chatBox || !container || !form) return;

            const textarea = form.querySelector('textarea');

            let loadingOlder = false;

            /* =========================
                FORMAT TIME
            ========================= */
            function formatTime(ts) {
                const diff = Date.now() - ts * 1000;

                const seconds = Math.floor(diff / 1000);
                const minutes = Math.floor(seconds / 60);
                const hours = Math.floor(minutes / 60);
                const days = Math.floor(hours / 24);

                if (seconds < 60) return `${seconds} sec ago`;
                if (minutes < 60) return `${minutes} min ago`;
                if (hours < 24) return `${hours} hr ago`;
                return `${days} day(s) ago`;
            }

            function updateTimes() {
                document.querySelectorAll('.time').forEach(el => {
                    const ts = parseInt(el.dataset.time);
                    if (!ts) return;

                    el.textContent = formatTime(ts);
                });
            }

            function scrollToBottom() {
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            function updateLastId() {
                const items = container.querySelectorAll('[data-id]');
                if (items.length) {
                    container.dataset.lastId = items[items.length - 1].dataset.id;
                }
            }

            /* =========================
                SEND MESSAGE
            ========================= */
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const message = textarea.value.trim();
                if (!message) return;

                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: new FormData(form)
                });

                const html = await res.text();

                container.insertAdjacentHTML('beforeend', html);

                updateLastId();
                updateTimes();
                scrollToBottom();

                textarea.value = '';
            });

            /* =========================
                NEW MESSAGES
            ========================= */
            setInterval(async () => {

                const lastId = container.dataset.lastId;
                if (!lastId) return;

                const res = await fetch(`/chat/{{ $chat->id }}/new-messages?last_id=${lastId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const html = await res.text();

                if (!html.trim()) return;

                container.insertAdjacentHTML('beforeend', html);

                updateLastId();
                updateTimes();
                scrollToBottom();

            }, 1500);

            /* =========================
                OLDER MESSAGES
            ========================= */
            chatBox.addEventListener('scroll', async () => {

                if (chatBox.scrollTop > 50 || loadingOlder) return;

                const firstId = container.dataset.firstId;
                if (!firstId) return;

                loadingOlder = true;

                const oldHeight = chatBox.scrollHeight;

                const res = await fetch(
                    `/chat/{{ $chat->id }}/older-messages?first_id=${firstId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );

                const html = await res.text();

                if (html.trim()) {

                    container.insertAdjacentHTML('afterbegin', html);

                    const items = container.querySelectorAll('[data-id]');
                    container.dataset.firstId = items[0].dataset.id;

                    chatBox.scrollTop = chatBox.scrollHeight - oldHeight;
                }

                loadingOlder = false;
            });

            /* =========================
                INIT
            ========================= */
            scrollToBottom();
            updateTimes();

            setInterval(updateTimes, 1000);
        });
    </script>
@endif