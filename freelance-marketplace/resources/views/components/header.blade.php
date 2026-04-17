<header
    class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between py-3 mb-4 border-bottom">
    <div class="col-md-3 mb-2 mb-md-0">
        <a href="{{ route('home.index') }}" class="d-inline-flex link-body-emphasis text-decoration-none">
            <img src="{{ asset('storage/images/f.png') }}" alt="Logo" class="bi ml-3" width="50" height="32" role="img" aria-label="Bootstrap">
        </a>
    </div>

    <ul class="nav col-12 col-md-auto mb-2 justify-content-center mb-md-0">
        <a href="{{ route('home.index') }}" @class([
            'nav-link px-2',
            'link-secondary' => request()->routeIs('home.index'),
        ])>
            Home
        </a>

        @auth
            @if (auth()->user()->UserRole->name === 'customer' || auth()->user()->UserRole->name === 'executor')
                <a href="{{ route('order.show-orders') }}" @class([
                    'nav-link px-2',
                    'link-secondary' => request()->routeIs('order.show-orders'),
                ])>
                    My orders
                </a>

                <a href="{{ route('chat.index') }}" @class([
                    'nav-link px-2',
                    'link-secondary' => request()->routeIs('chat.*'),
                ])>
                    My chats
                </a>
            @endif

            @if (auth()->user()->UserRole->name === 'executor')
                <a href="{{ route('order.show-proposals') }}" @class(['nav-link px-2', 'link-secondary' => request()->routeIs('order.show-proposals'),])>
                    My proposals
                </a>
            @endif
            
            @if (auth()->user()->UserRole->name === 'customer')
                <a href="{{ route('order.create-order') }}" @class([
                    'nav-link px-2',
                    'link-secondary' => request()->routeIs('order.create-order'),
                ])>
                    Create an order
                </a>
            @endif

            <a href="{{ route('profile.index') }}" @class(['nav-link px-2', 'link-secondary' => request()->routeIs('profile.*'),])>
                My profile
            </a>
        @endauth

        @guest
            <li><a href="#" class="nav-link px-2">Pricing</a></li>
            <li><a href="#" class="nav-link px-2">Features</a></li>
            <li><a href="#" class="nav-link px-2">FAQs</a></li>
        @endguest
    </ul>

    <div class="col-md-3 text-end mr-3">
        @guest
            <a href="{{ route('login') }}"><button type="button" class="btn btn-outline-primary me-2">Login</button></a>
            <a href="{{ route('register') }}"><button type="button" class="btn btn-primary">Sign-up</button></a>
        @endguest

        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-primary">
                    Logout
                </button>
            </form>
        @endauth
    </div>
</header>