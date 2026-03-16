@extends('main')

@section('content')
    <div class="container my-4">
        @php
            $sortType = request()->query('sortType', 'default');

            $sortNames = [
                'byTimeAsc' => 'Sort by Time ↑ (oldest first)',
                'byTimeDesc' => 'Sort by Time ↓ (newest first)',
                'byBudgetAsc' => 'Sort by Budget ↑ (lowest first)',
                'byBudgetDesc' => 'Sort by Budget ↓ (highest first)',
                'byStatus' => 'Sort by Status',
                'default' => 'Sort Orders',
            ];
            
            $generalSortTypeText = $sortNames[$sortType] ?? 'Sort Orders';

            function getColorForOrderStatus($orderStatusName) 
            {
                switch ($orderStatusName) {
                    case 'published':
                        return 'bg-success';
                    case 'in_progress':
                        return 'bg-warning';
                    case 'completed':
                        return 'bg-info';
                    case 'cancelled':
                        return 'bg-danger';
                    default:
                        return 'bg-secondary';
                }
            }
        @endphp

        <div class="d-flex justify-content-end gap-2 mb-4">
            {{-- Order Status Filter Dropdown --}}
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ $currentOrderStatusFilter->name }}
                </button>

                <ul class="dropdown-menu">
                    @foreach ($uniqueOrderStatuses as $orderStatus)
                        <li>
                            <a class="dropdown-item" href="{{ route('order.show-orders', ['p' => $orders->currentPage(), 'filterSubcategory' => $currentSubcategoryFilter->id, 'filterOrderStatus' => $orderStatus->id]) }}">
                                {{ $orderStatus->id }} - {{ $orderStatus->name }}
                            </a>
                        </li>
                    @endforeach

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item"
                            href="{{ route('order.show-orders', ['p' => $orders->currentPage(), 'filterSubcategory' => $currentSubcategoryFilter->id]) }}">
                            Clear Order Status Filter
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Subcategory Filter Dropdown --}}
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ $currentSubcategoryFilter->name }}
                </button>

                <ul class="dropdown-menu">
                    @foreach ($uniqueSubcategories as $subcategory)
                        <li>
                            <a class="dropdown-item"
                                href="{{ route('order.show-orders', ['p' => $orders->currentPage(), 'filterSubcategory' => $subcategory->id, 'filterOrderStatus' => $currentOrderStatusFilter->id]) }}">
                                {{ $subcategory->mainOrderCategory->name }} - {{ $subcategory->name }}
                            </a>
                        </li>
                    @endforeach

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item"
                            href="{{ route('order.show-orders', ['p' => $orders->currentPage()]) }}">
                            Clear Subcategory Filter
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Sort Type Dropdown --}}
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu3"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    {{ $generalSortTypeText }}
                </button>

                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item"
                            href="{{ route('order.show-orders', ['p' => $orders->currentPage(), 'sortType' => 'byTimeAsc', 'filterSubcategory' => $currentSubcategoryFilter->id, 'filterOrderStatus' => $currentOrderStatusFilter->id]) }}">
                            Sort by Time ↑ (oldest first)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item"
                            href="{{ route('order.show-orders', ['p' => $orders->currentPage(), 'sortType' => 'byTimeDesc', 'filterSubcategory' => $currentSubcategoryFilter->id, 'filterOrderStatus' => $currentOrderStatusFilter->id]) }}">
                            Sort by Time ↓ (newest first)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item"
                            href="{{ route('order.show-orders', ['p' => $orders->currentPage(), 'sortType' => 'byBudgetAsc', 'filterSubcategory' => $currentSubcategoryFilter->id, 'filterOrderStatus' => $currentOrderStatusFilter->id]) }}">
                            Sort by Budget ↑ (lowest first)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item"
                            href="{{ route('order.show-orders', ['p' => $orders->currentPage(), 'sortType' => 'byBudgetDesc', 'filterSubcategory' => $currentSubcategoryFilter->id, 'filterOrderStatus' => $currentOrderStatusFilter->id]) }}">
                            Sort by Budget ↓ (highest first)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item"
                            href="{{ route('order.show-orders', ['p' => $orders->currentPage(), 'sortType' => 'byStatus', 'filterSubcategory' => $currentSubcategoryFilter->id, 'filterOrderStatus' => $currentOrderStatusFilter->id]) }}">
                            Sort by Status
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12">
                {{-- Orders List --}}
                @if ($orders_total_count === 0)
                    <div class="alert alert-info text-center">
                        You have no orders yet. <a href="{{ route('order.create-order') }}" class="alert-link">Create your
                            first order</a>.
                    </div>
                @else
                    @if (count($orders) === 0)
                        <div class="alert alert-info text-center">
                            No orders found on this page.
                        </div>
                    @endif

                    @foreach ($orders as $order)
                        <div class="card shadow-sm mb-4 border border-secondary bg-dark text-light">
                            {{-- Header --}}
                            <div
                                class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center">
                                <h5 class="mt-2 mb-2 fw-semibold text-light">
                                    {{ $order->title }}
                                </h5>

                                {{-- Categories --}}
                                <div>
                                    <span class="badge bg-success p-2">
                                        {{ $order->created_at->format('Y-m-d H:i') }}
                                    </span>
                                    <span class="badge {{ getColorForOrderStatus($order->status->name) }} p-2">
                                        {{ $order->status->name }}
                                    </span>
                                </div>
                            </div>

                            {{-- Body --}}
                            <div class="card-body">
                                <p class="text-ligth mb-3">{{ $order->short_description }}</p>

                                @if ($order->requirement_skills)
                                    <b>Requirment skills:</b>
                                    <p class="text-ligth mt-1 mb-2">{{ $order->requirement_skills }}</p>
                                @endif
                            </div>

                            {{-- Footer --}}
                            <div
                                class="card-footer bg-dark border-secondary d-flex justify-content-between align-items-center">

                                <div>
                                    <span class="badge bg-primary p-2">{{ $order->subCategory->mainOrderCategory->name }} -
                                        {{ $order->subCategory->name }}</span>

                                    <span class="badge bg-success p-2">{{ $order->budget }} USD </span>

                                    <span class="badge bg-warning text-dark p-2">{{ $order->deadline_in_days }}
                                        day(s)
                                    </span>
                                </div>

                                <a href="{{ route('order.show-order', $order) }}" class="btn btn-info btn-sm pl-2 pr-2 text">
                                    View Details →
                                </a>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <div class="mt-4 text-center">
        {{ $orders->appends(request()->except('p'))->links('vendor.pagination.bootstrap-5-dark') }}
    </div>
@endsection