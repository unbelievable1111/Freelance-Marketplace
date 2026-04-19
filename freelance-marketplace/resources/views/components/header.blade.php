@php
    use App\Models\Notification;
@endphp

<header class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom flex-wrapmb-5 mb-4">
    <!-- LOGO -->
    <div class="d-flex align-items-center">
        <a href="{{ route('home.index') }}" class="d-flex align-items-center text-decoration-none">
            <img src="{{ asset('storage/images/f.png') }}" alt="Logo" width="40" height="26">
        </a>
    </div>

    <!-- NAV -->
    <ul class="nav d-flex align-items-center gap-1 flex-wrap justify-content-center my-1  p-3 ">
        <li>
            <a href="{{ route('home.index') }}"
                class="nav-link px-2 py-1 {{ request()->routeIs('home.index') ? 'link-secondary' : '' }}">
                Home
            </a>
        </li>

        @auth
            @if (in_array(auth()->user()->UserRole->name, ['customer', 'executor']))
                <li>
                    <a href="{{ route('order.show-orders') }}"
                        class="nav-link px-2 py-1 {{ request()->routeIs('order.show-orders') ? 'link-secondary' : '' }}">
                        My orders
                    </a>
                </li>

                <li>
                    <a href="{{ route('chat.index') }}"
                        class="nav-link px-2 py-1 {{ request()->routeIs('chat.*') ? 'link-secondary' : '' }}">
                        My chats
                    </a>
                </li>
            @endif

            @if (auth()->user()->UserRole->name === 'executor')
                <li>
                    <a href="{{ route('order.show-proposals') }}"
                        class="nav-link px-2 py-1 {{ request()->routeIs('order.show-proposals') ? 'link-secondary' : '' }}">
                        My proposals
                    </a>
                </li>
            @endif

            @if (auth()->user()->UserRole->name === 'customer')
                <li>
                    <a href="{{ route('order.create-order') }}"
                        class="nav-link px-2 py-1 {{ request()->routeIs('order.create-order') ? 'link-secondary' : '' }}">
                        Create order
                    </a>
                </li>
            @endif

            <li>
                <a href="{{ route('profile.index') }}"
                    class="nav-link px-2 py-1 {{ request()->routeIs('profile.*') ? 'link-secondary' : '' }}">
                    My profile
                </a>
            </li>


        @endauth

        <li><a href="#" class="nav-link px-2 py-1">Pricing</a></li>
        <li><a href="#" class="nav-link px-2 py-1">Features</a></li>
        <li><a href="#" class="nav-link px-2 py-1">FAQs</a></li>

        @auth
            <li>
                <a href="{{ route('notifications.index') }}"
                    class="nav-link px-2 py-1 {{ request()->routeIs('notifications.*') ? 'link-secondary' : '' }}">
                    Notifications 
                    
                    <span id="notification-badge"
                        class="badge bg-danger p-2"
                        style="{{ Notification::getUnreadAmount(auth()->user()) > 0 ? '' : 'display:none;' }}">
                        {{ Notification::getUnreadAmount(auth()->user()) }}
                    </span>
                </a>
            </li>
        @endauth
    </ul>

    <!-- AUTH BUTTONS -->
    <div class="d-flex align-items-center gap-2">
        @guest
            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Login</a>
            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Sign up</a>
        @endguest

        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    Logout
                </button>
            </form>
        @endauth
    </div>
</header>

<script>
    const unreadUrl = "{{ route('notifications.unread-count') }}";
    let lastCount = null;

    const notificationSound = new Audio('/sounds/notify.mp3'); // положи файл в public/sounds

    function updateNotificationCount() {
        if (document.hidden) return;

        fetch(unreadUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok || response.redirected) {
                return null;
            }
            return response.json();
        })
        .then(data => {
            if (!data) return;

            const badge = document.getElementById('notification-badge');
            if (!badge) return;

            if (lastCount !== null && data.count > lastCount) {
                notificationSound.play().catch(() => {});
            }

            lastCount = data.count;

            if (data.count > 0) {
                badge.style.display = 'inline-block';
                badge.textContent = data.count;
            } else {
                badge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error:', error));
    }

    setInterval(updateNotificationCount, 5000);
    updateNotificationCount();
</script>