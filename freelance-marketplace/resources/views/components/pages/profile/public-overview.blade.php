@extends('main')

@section('content')
<div class="container mt-4">

    {{-- Avatar --}}
        <div class="row justify-content-center mb-4">
            <div class="col-12 ">
                <div class="card shadow-sm">
                    <div class="card-header bg-info-subtle text-center">
                        <h5 class="mb-0 text-white">{{ $publicUser->name }}</h5>
                    </div>
                    <div class="card-body text-center">
                        <!-- Current user's avatar -->
                        <div class="d-flex justify-content-center mb-3">
                            <img src="{{ $publicUser->avatar }}" alt="User's avatar"  class="rounded-circle"  style="width: 200px; height: 200px; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    {{-- Profile info --}}
    <div class="row justify-content-center mb-4">
        <div class="col-12">
            <div class="card shadow-sm">

                <div class="card-header bg-success text-center">
                    <h5 class="mb-0 text-white">Profile Information</h5>
                </div>

                <div class="card-body">

                    <div class="mb-3">
                        <small class="text-muted">Name</small>
                        <div class="fs-5">{{ $publicUser->name }}</div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Role</small>
                        <div class="fs-5">{{ $publicUser->UserRole->name }}</div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Average Rating</small>

                        @if ($publicUser->Reviews->count() > 0)
                            <div class="fs-5">
                                {{ (int) $publicUser->getAverageRatingAttribute() }}
                                / 5 ({{ $publicUser->Reviews->count() }} reviews)
                            </div>
                        @else
                            <div class="fs-5">No reviews yet</div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Reviews --}}
    @include('components.pages.profile.reviews')
</div>
@endsection