@php
    $sortType = request()->query('proposalSortType', 'default');

    $sortNames = 
    [
        'byTimeAsc' => 'Sort by Time ↑ (oldest first)',
        'byTimeDesc' => 'Sort by Time ↓ (newest first)',
        'byBudgetAsc' => 'Sort by Budget ↑ (lowest first)',
        'byBudgetDesc' => 'Sort by Budget ↓ (highest first)',
        'byDeadlineAsc' => 'Sort by Deadline ↑ (soonest first)',
        'byDeadlineDesc' => 'Sort by Deadline ↓ (latest first)',
        'default' => 'Sort Proposals',
    ];

    $generalSortTypeText = $sortNames[$sortType] ?? 'Sort Orders';
@endphp

@if (session('error-order-approve'))
    <div class="alert alert-danger mt-4">
        {{ session('error-order-approve') }}
    </div>
@endif

{{-- Approves Section --}}
@if ($approves->isNotEmpty())
    <div class="card shadow-sm mb-4 border border-secondary bg-dark text-light mt-4">
        {{-- Header --}}
        <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center">
            <h5 class="mt-2 mb-2 fw-semibold text-light">Proposals</h5>

            <div class="d-flex align-items-center gap-2">
                {{-- start --}}
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu3"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        {{ $generalSortTypeText }}
                    </button>

                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item"
                                href="{{ route('order.show-order', ['order' => $order, 'p' => $approves->currentPage(), 'proposalSortType' => 'byTimeAsc']) }}">
                                Sort by Time ↑ (oldest first)
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item"
                                href="{{ route('order.show-order', ['order' => $order, 'p' => $approves->currentPage(), 'proposalSortType' => 'byTimeDesc']) }}">
                                Sort by Time ↓ (newest first)
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item"
                                href="{{ route('order.show-order', ['order' => $order, 'p' => $approves->currentPage(), 'proposalSortType' => 'byBudgetAsc']) }}">
                                Sort by Budget ↑ (lowest first)
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item"
                                href="{{ route('order.show-order', ['order' => $order, 'p' => $approves->currentPage(), 'proposalSortType' => 'byBudgetDesc']) }}">
                                Sort by Budget ↓ (highest first)
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item"
                                href="{{ route('order.show-order', ['order' => $order, 'p' => $approves->currentPage(), 'proposalSortType' => 'byDeadlineAsc']) }}">
                                Sort by Deadline ↑ (soonest first)
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item"
                                href="{{ route('order.show-order', ['order' => $order, 'p' => $approves->currentPage(), 'proposalSortType' => 'byDeadlineDesc']) }}">
                                Sort by Deadline ↓ (latest first)
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card-body">
            <ul class="list-group list-group-flush">
                @foreach ($approves as $approve)
                    <li class="list-group-item bg-dark text-light d-flex justify-content-between align-items-center">
                        <div class="w-100">
                            <div class="d-flex justify-content-between align-items-center">
                                {{-- LEFT: User + Date --}}
                                <div>
                                    <strong>
                                        <a href="{{ route('public-profile.overview', $approve->user) }}"
                                            class="text-decoration-none text-light">
                                            {{ $approve->user->name }}

                                        </a>
                                    </strong>

                                    <span class="text-muted small ms-2">
                                        {{ $approve->created_at->format('Y-m-d H:i') }}
                                    </span>

                                    <div class="badge  text-dark p-2">
                                        @for ($i = 0; $i < 5; $i++)
                                            <span
                                                class="{{ $i < (int) $approve->user->getAverageRatingAttribute() ? 'text-warning' : 'text-secondary' }}">
                                                ★
                                            </span>
                                        @endfor
                                    </div>
                                </div>

                                {{-- RIGHT: Badges + Buttons --}}
                                <div class="d-flex align-items-center gap-1">
                                    <span class="badge bg-warning text-dark p-2">
                                        {{ $approve->proposed_deadline_in_days }} day(s)
                                    </span>

                                    <span class="badge bg-success p-2">
                                        {{ $approve->proposed_budget }} USD
                                    </span>

                                    @if (Auth::check() && Auth::id() === $order->customer_id)
                                        <form action="{{ route('chat.start-chat', [$order, 'receiver' => $approve->user_id]) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="badge bg-info p-2" data-bs-toggle="modal">
                                                Start chat
                                            </button>
                                        </form>

                                        <form action="{{ route('order.approval-submit', [$order, $approve]) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="badge bg-primary p-2" data-bs-toggle="modal">
                                                Submit Proposal
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            @if ($approve->comment)
                                <p class="mb-0 text-muted small mt-1 pt-3 pb-3" style="text-align: justify;">
                                    {{ $approve->comment }}
                                </p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="card-footer bg-dark border-secondary d-flex justify-content-center p-3">
            {{-- Pagination info --}}
            @if ($approves->hasPages())
                <div class="bg-dark border-secondary">
                    <div class="d-flex justify-content-center">
                        {{ $approves->appends(request()->except('p'))->links('vendor.pagination.bootstrap-5-dark') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
