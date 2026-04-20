@extends('main')

@php
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
            case 'expired':
                return 'bg-secondary';
            default:
                return 'bg-secondary';
        }
    }
@endphp

@section('content')
    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-sm mb-4 border border-secondary bg-dark text-light">
                    {{-- Header --}}
                    <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center">
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
                        <b>Short description:</b>
                        <p class="text-ligth mb-3">{{ $order->short_description }}</p>

                        <b>Requirment skills:</b>
                        <p class="text-ligth mt-1 mb-2">{{ $order->requirement_skills }}</p>

                        <b>Full description:</b>
                        <p class="text-ligth mt-1 mb-2">{{ $order->full_description }}</p>

                        <b>Attachments:</b>
                        @if ($order->fileAttachments->isNotEmpty())
                            <div class="d-flex flex-wrap gap-3 mt-2">
                                <ul>
                                    @foreach ($order->fileAttachments as $attachment)
                                        <li>
                                            <a href="{{ asset('storage/public_order_attachments/' . $attachment->stored_filename) }}"
                                                target="_blank">
                                                — {{ $attachment->original_filename }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <p>No attachments available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="card-footer bg-dark border-secondary d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-primary p-2">{{ $order->subCategory->mainOrderCategory->name }} -
                    {{ $order->subCategory->name }}</span>

                <span class="badge bg-success p-2">{{ $order->budget }} USD</span>

                <span class="badge bg-warning text-dark p-2">{{ $order->deadline_in_days }}
                    day(s)
                </span>

                <span class="badge bg-light text-dark p-2">
                    <a href="{{ route('public-profile.overview', $order->customer) }}"
                        class="text-decoration-none text-dark">
                        {{ $order->customer->name }}
                    </a>
                </span>

                <div class="badge bg-light text-dark p-2">
                    @for ($i = 0; $i < 5; $i++)
                        <span
                            class="{{ $i < (int) $order->customer->getAverageRatingAttribute() ? 'text-warning' : 'text-secondary' }}">
                            ★
                        </span>
                    @endfor
                </div>

                <span class="badge bg-danger text-dark p-2">
                    <a href="{{ route('report.create', $order) }}" class="text-decoration-none text-dark">
                        Make a report
                    </a>
                </span>
            </div>

            @if (url()->previous() !== url()->current())
                <a href="{{ url()->previous() }}" class="btn btn-info btn-sm ps-2 pe-2">
                    ← Previous Page
                </a>
            @endif
        </div>

        @if (session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif

        @if ($order->executor && $order->userBelongsToOrder())
            @include('components.pages.orders.progress-card')
        @endif

        @if ($order->isPublished() && auth()->user()->UserRole->name === 'executor')
            @if ($order->orderApproves()->where('user_id', auth()->id())->exists())
                @include('components.pages.orders.approves.my-approve-form')
            @else
                @include('components.pages.orders.approves.approve-form')
            @endif

            @include('components.pages.orders.approves.order-approves')
        @endif

        @if ($order->isCompleted() && $order->userBelongsToOrder())
            @include('components.pages.orders.review-card')
        @endif



        @if (session('error'))
            <div class="alert alert-danger mt-4">
                {{ session('error') }}
            </div>
        @endif
    </div>
@endsection
