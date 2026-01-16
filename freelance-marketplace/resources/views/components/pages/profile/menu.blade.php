@extends('main')

@section('content')
    <div class="container">
        <div class="row">
            <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark col-3 min-width: 325px">
                <a href="{{ route('profile.index') }}"
                    class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                    <img src="{{ asset('storage/images/profile_ico.png') }}" alt="Logo" class="bi mr-3" width="50"
                        height="32" role="img" aria-label="Bootstrap">
                    <span class="fs-4">My profile</span>
                </a>
                <hr>
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a href="{{ route('profile.index') }}"
                            class="nav-link {{ request()->routeIs('profile.index') ? 'active' : 'text-white' }}">
                            General information
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('profile.bank-accounts') }}"
                            class="nav-link {{ request()->routeIs('profile.bank-accounts') ? 'active' : 'text-white' }}">
                            Bank accounts
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('profile.transactions.finance-operations') }}"
                            class="nav-link {{ request()->routeIs('profile.transactions.finance-operations') ? 'active' : 'text-white' }}">
                            Finance operations
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('profile.transactions.history', ['order' => 'desc', 'page' => 1]) }}"
                            class="nav-link {{ request()->routeIs('profile.transactions.history') ? 'active' : 'text-white' }}">
                            Transactions
                        </a>
                    </li>

                    {{-- 
                        <li class="nav-item">
                            <a href="{{ route('profile.transactions') }}"
                            class="nav-link {{ request()->routeIs('profile.transactions') ? 'active' : 'text-white' }}">
                                Transaction history
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('profile.reviews') }}"
                            class="nav-link {{ request()->routeIs('profile.reviews') ? 'active' : 'text-white' }}">
                                Reviews
                            </a>
                        </li> 
                    --}}
                </ul>
                <hr>
            </div>
            <div class="col-9">
                @yield('profile-content')
            </div>
        </div>
    </div>
@endsection
