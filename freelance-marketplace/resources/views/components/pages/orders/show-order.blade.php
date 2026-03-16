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
                                            <a href="{{ asset('storage/public_order_attachments/' . $attachment->stored_filename) }}" target="_blank">
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

                {{-- Footer --}}
                <div class="card-footer bg-dark border-secondary d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary p-2">{{ $order->subCategory->mainOrderCategory->name }} - {{ $order->subCategory->name }}</span>

                        <span class="badge bg-success p-2">{{ $order->budget }} USD </span>

                        <span class="badge bg-warning text-dark p-2">{{ $order->deadline_in_days }}
                            day(s)
                        </span>

                        <span class="badge bg-light text-dark p-2">
                            {{ $order->user->name }}
                        </span>
                    </div>

                    @if(url()->previous() !== url()->current())
                        <a href="{{ url()->previous() }}" class="btn btn-info btn-sm ps-2 pe-2">
                            ← Previous Page
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection