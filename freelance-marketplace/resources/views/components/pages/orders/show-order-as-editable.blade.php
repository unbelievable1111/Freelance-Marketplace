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
                {{-- Edit form --}}
                <form method="POST" action="{{ route('order.edit-order', $order) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card shadow-sm mb-4 border border-secondary bg-dark text-light">
                        {{-- Header --}}
                        <div class="card-header bg-dark border-secondary d-flex align-items-center">
                            <input type="text" name="title"
                                class="form-control form-control-sm bg-dark text-light border-secondary me-3 flex-grow-1 fs-5"
                                value="{{ old('title', $order->title) }}" required>

                            <span class="badge bg-success p-3 me-2">
                                {{ $order->created_at->format('Y-m-d H:i') }}
                            </span>
                            <span class="badge {{ getColorForOrderStatus($order->status->name) }} p-3">
                                {{ $order->status->name }}
                            </span>
                        </div>

                        {{-- Body --}}
                        <div class="card-body">
                            {{-- CATEGORY --}}
                            <div class="mb-3">
                                <label class="form-label"><b>Category:</b></label>
                                <div>
                                    <select class="form-select text-center" name="sub_category_id" required>
                                        @foreach ($subCategories as $subCategory)
                                            <option value="{{ $subCategory->id }}"
                                                {{ $order->sub_category_id == $subCategory->id ? 'selected' : '' }}>
                                                {{ $subCategory->name }} — {{ $subCategory->mainOrderCategory->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><b>Short description:</b></label>
                                <textarea name="short_description" class="form-control bg-dark text-light border-secondary" maxlength="250"
                                    rows="2">{{ old('short_description', $order->short_description) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><b>Requirement skills:</b></label>
                                <textarea name="requirement_skills" class="form-control bg-dark text-light border-secondary" maxlength="250"
                                    rows="2">{{ old('requirement_skills', $order->requirement_skills) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><b>Full description:</b></label>
                                <textarea name="full_description" class="form-control bg-dark text-light border-secondary" maxlength="5000"
                                    rows="5">{{ old('full_description', $order->full_description) }}</textarea>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="card-footer bg-dark border-secondary d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-3 flex-grow-1">
                                {{-- BUDGET --}}
                                <div class="d-flex flex-column flex-grow-1">
                                    <label class="form-label text-light mb-1">Budget (USD):</label>
                                    <input type="number" name="budget"
                                        class="form-control bg-dark text-light border-secondary text-center"
                                        value="{{ old('budget', $order->budget) }}" min="5" step="0.01" required>
                                </div>

                                {{-- DEADLINE --}}
                                <div class="d-flex flex-column flex-grow-1">
                                    <label class="form-label text-light mb-1">Deadline (days):</label>
                                    <input type="number" name="deadline_in_days"
                                        class="form-control bg-dark text-light border-secondary text-center"
                                        value="{{ old('deadline_in_days', $order->deadline_in_days) }}" min="1"
                                        step="1" required>
                                </div>
                            </div>

                            <div class="ms-3 d-flex flex-column justify-content-end">
                                <button type="submit" class="btn btn-success btn-sm mb-1">Update Order</button>
                                <a href="{{ route('order.show-order', $order) }}"
                                    class="btn btn-secondary btn-sm">Cancel</a>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-sm mb-4 border border-secondary bg-dark text-light">
                    {{-- Header --}}
                    <div class="card-header bg-dark border-secondary d-flex align-items-center">
                        <h5 class="mt-2 mb-2 fw-semibold text-light">
                            Attachments
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            @if ($order->fileAttachments->isNotEmpty())
                                <ul>
                                    @foreach ($order->fileAttachments as $attachment)
                                        <li>
                                            <a href="{{ asset('storage/public_order_attachments/' . $attachment->stored_filename) }}"
                                                target="_blank">
                                                {{ $attachment->original_filename }}

                                                <form method="POST"
                                                    action="{{ route('order.delete-attachment', $attachment) }}"
                                                    class="d-inline ms-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-link p-0 link-danger link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">
                                                        Delete
                                                    </button>
                                                </form>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p>No attachments available.</p>
                            @endif

                            <form method="POST" action="{{ route('order.add-attachment', $order) }}"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="attachments[]"
                                    class="form-control bg-dark text-light border-secondary mt-2" multiple>
                                <button type="submit" class="btn btn-primary btn-sm mt-2 w-100 p-3 mt-3">Add
                                    Attachments</button>
                            </form>

                            @if (session('error'))
                                <div class="alert alert-danger mt-4">
                                    {{ session('error') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($order->status->name == 'published')
            <form action="{{ route('order.cancel-order', $order) }}" method="POST">
                @csrf
                @method('PATCH')

                <button type="submit" class="btn btn-danger w-100 p-2">
                    Cancel order
                </button>
            </form>
        @endif
    </div>
@endsection
