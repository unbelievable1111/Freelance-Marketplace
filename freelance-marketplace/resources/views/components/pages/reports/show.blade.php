@extends('main')

@section('content')
<div class="container py-4">

    <h2 class="mb-4">Report Details</h2>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            @if(is_array(session('error')))
                <ul class="mb-0">
                    @foreach(session('error') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            @else
                {{ session('error') }}
            @endif
        </div>
    @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Report details --}}
    <div class="card mb-4">
        <div class="card-body">

            <h4 class="card-title mb-3">
                {{ $report->title }}
            </h4>

            @php
                $status = $report->status->name;
                $badgeClass = match($status) {
                    'in_progress' => 'bg-warning text-dark',
                    'completed' => 'bg-success',
                    default => 'bg-secondary'
                };
            @endphp

            <p class="mb-2">
                <strong>Status:</strong>
                <span class="badge {{ $badgeClass }}">
                    {{ $status }}
                </span>
            </p>

            <p class="mb-2">
                <strong>Reported by:</strong>
                <a href="{{ route('public-profile.overview', $report->reporter->id) }}">
                    {{ $report->reporter->name }}
                </a>
            </p>

            <p class="mb-2">
                <strong>Created at:</strong>
                {{ $report->created_at->format('Y-m-d H:i') }}
            </p>

            <hr>

            <p>
                <strong>Description:</strong><br>
                {{ $report->description ?? 'No description provided.' }}
            </p>

        </div>
    </div>

    {{-- Admin action: mark report as complete --}}
    @if (auth()->user()->isAdmin())
        <form method="POST" action="{{ route('report.complete', $report) }}">
            @csrf
            @method('PATCH')

            <button 
                type="submit" 
                class="btn btn-success mb-4 w-100"
                {{ $report->status->name === 'complete' ? 'disabled' : '' }}
            >
                Mark as Complete
            </button>
        </form>
    @endif

    {{-- Related order --}}
    <div class="card mb-4">
        <div class="card-body">
            <h5>Related Order</h5>

            <p class="mb-2">
                <strong>Title:</strong>
                <a href="{{ route('order.show-order', $report->order->id) }}">
                    {{ $report->order->title }}
                </a>
            </p>

            @if($report->order->description)
                <p class="mb-0">
                    <strong>Description:</strong><br>
                    {{ \Illuminate\Support\Str::limit($report->order->description, 200) }}
                </p>
            @endif
        </div>
    </div>

    {{-- Comments section --}}
    <div class="card">
        <div class="card-body">

            <h5 class="mb-3">
                Comments ({{ $comments->total() }})
            </h5>

            @forelse($comments as $comment)
                <div class="mb-3 p-3 border rounded">

                    <div class="d-flex justify-content-between">
                        <strong>
                            <a href="{{ route('public-profile.overview', $comment->user->id) }}">
                                {{ $comment->user->name }}
                            </a>
                        </strong>

                        <small class="text-muted">
                            {{ $comment->created_at->diffForHumans() }}
                        </small>
                    </div>

                    <p class="mt-2 mb-0">
                        {{ $comment->content }}
                    </p>

                </div>
            @empty
                <p class="text-muted">No comments yet.</p>
            @endforelse

            {{-- Pagination --}}
            <div class="mt-4 text-center">
                {{ $comments->links('vendor.pagination.bootstrap-5-dark') }}
            </div>

            {{-- Comment form --}}
            <hr>

            <h5 class="mt-4">Add Comment</h5>

            <form method="POST" action="{{ route('report.comment.store', $report->id) }}">
                @csrf

                <div class="mb-3">
                    <textarea 
                        name="content" 
                        class="form-control @error('content') is-invalid @enderror" 
                        rows="3" 
                        placeholder="Write your comment..."
                        required
                    >{{ old('content') }}</textarea>

                    @error('content')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    Submit Comment
                </button>
            </form>

        </div>
    </div>
</div>
@endsection