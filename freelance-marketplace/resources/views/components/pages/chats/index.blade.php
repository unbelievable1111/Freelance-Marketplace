@extends('main')

@section('content')
    <div class="container">
        <div class="row">

            <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark col-3 min-width: 325px">

                <ul class="nav nav-pills flex-column">
                    @if ($chats->isEmpty())
                        <li class="nav-item">
                            <hr>
                            <span class="nav-link text-white-50 text-center">
                                No chats yet
                            </span> 
                            <hr>
                        </li>
                    @endif

                    @foreach ($chats as $_chat)
                        <li class="nav-item">
                            <a href="{{ route('chat.show', $_chat) }}"
                                class="nav-link {{ request()->routeIs('chat.show') && request()->route('chat')->id === $_chat->id ? 'active' : 'text-white' }}">

                                {{ $_chat->order->title }} - {{ $_chat->creator_id === auth()->id() ? $_chat->participant->name : $_chat->creator->name }}

                                <br>

                                <small class="text-muted">
                                    {{ $_chat->getLastMessageAttribute()
                                        ? $_chat->getLastMessageAttribute()->created_at->diffForHumans()
                                        : 'No messages yet' }}
                                </small>

                                @if (($chat->id ?? null) != $_chat->id)
                                    <span class="badge bg-primary m-1 d-none" id="unread-{{ $_chat->id }}">
                                        New
                                    </span>
                                @endif
                            </a>
                            <hr>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-4 text-center">
                    {{ $chats->appends(request()->except('p'))->links('vendor.pagination.bootstrap-5-dark') }}
                </div>

            </div>

            <div class="col-9 p-0">
                @include('components.pages.chats.particular-chat')
            </div>

        </div>
    </div>
@endsection



<script>
    window.updateUnreadChats = async function() {
        try 
        {
            const res = await fetch('/chat-statuses/unread-status', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await res.json();

            for (const chatId in data) {
                const el = document.getElementById(`unread-${chatId}`);
                if (!el) continue;

                el.classList.toggle('d-none', !data[chatId]);
            }
        } 
        catch (e) {
            console.error('Unread update error:', e);
        }
    };

    // INIT
    document.addEventListener('DOMContentLoaded', () => 
    {
        updateUnreadChats();
        setInterval(updateUnreadChats, 3000);
    });
</script>