{{-- Body --}}
<div class="card-body text-center">
    {{--
        Logic:
        - If user is customer and has already left a review for executor, show the review.
        - If user is executor and has already left a review for customer, show the review.
        - If user is customer and has not left a review for executor, show the form to leave a review.
        - If user is executor and has not left a review for customer, show the form to leave a review.
    --}}
    @if ($order->hasReviewForExecutor())
        <div class="mt-4 p-3 rounded bg-dark border">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="fw-semibold text-light">
                        Review for Executor ({{ $order->executor->name }})
                    </div>
                </div>

                <div class="text-dark bg-light fw-bold px-3 py-2 rounded-pill shadow-sm">
                    ⭐ {{ $order->reviewForExecutor()->score }} / 5
                </div>
            </div>

            <!-- Divider -->
            <hr class="border-secondary my-3">

            <!-- Feedback -->
            <div class="text-light" style="line-height: 1.7; font-size: 15px; text-align: justify;">
                {{ $order->reviewForExecutor()->feedback }}
            </div>

            <!-- Delete Button -->
            @if ((auth()->user()->UserRole->name === 'customer' && $order->hasReviewForExecutor()))
                <form method="POST" action="{{ route('order.delete-review', ['review' => $order->reviewForExecutor()]) }}"
                    onsubmit="return confirm('Are you sure you want to delete your review? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger mt-3 w-100">Delete Review</button>
                </form>
            @endif
        </div>
    @endif

    @if ($order->hasReviewForCustomer())
        <div class="mt-4 p-3 rounded bg-dark border">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="fw-semibold text-light">
                        Review for Customer ({{ $order->customer->name }})
                    </div>
                </div>

                <div class="text-dark bg-light fw-bold px-3 py-2 rounded-pill shadow-sm">
                    ⭐ {{ $order->reviewForCustomer()->score }} / 5
                </div>
            </div>

            <!-- Divider -->
            <hr class="border-secondary my-3">

            <!-- Feedback -->
            <div class="text-light" style="line-height: 1.7; font-size: 15px; text-align: justify;">
                {{ $order->reviewForCustomer()->feedback }}
            </div>

            <!-- Delete Button -->
            @if ((auth()->user()->UserRole->name === 'executor' && $order->hasReviewForCustomer()))
                <form method="POST" action="{{ route('order.delete-review', ['review' => $order->reviewForCustomer()]) }}"
                    onsubmit="return confirm('Are you sure you want to delete your review? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger mt-3 w-100">Delete Review</button>
                </form>
            @endif
        </div>
    @endif


    {{-- Show form to leave a review if user has not left a review for the other party. --}}
    @if ((auth()->user()->UserRole->name === 'customer' && !$order->hasReviewForExecutor()) ||
            (auth()->user()->UserRole->name === 'executor' && !$order->hasReviewForCustomer()))
        <div class="mt-4 p-3 rounded bg-dark border">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="fw-semibold text-light">
                        Review for
                        {{ auth()->user()->UserRole->name === 'customer' ? 'Executor' : 'Customer' }}
                        ({{ auth()->user()->UserRole->name === 'customer' ? $order->executor->name : $order->customer->name }})
                    </div>
                </div>
            </div>

            <!-- Divider -->
            <hr class="border-secondary my-3">
            <form method="POST" action="{{ route('order.leave-review', ['order' => $order]) }}">
                @csrf
                <div class="container">
                    {{-- Feedback --}}
                    <div class="row mt-3">
                        <div class="col-3 text-end mt-1 fw-bold">Feedback:</div>
                        <div class="col-9">
                            <textarea name="feedback" class="form-control" rows="10" maxlength="1500" required
                                placeholder="Enter your feedback for this order…">{{ old('feedback') }}</textarea>
                        </div>
                    </div>

                    {{-- SCORE --}}
                    <div class="row mt-3">
                        <div class="col-3 text-end mt-1 fw-bold">Score:</div>
                        <div class="col-9">
                            <input type="number" name="score" class="form-control text-center"
                                min="1" max="5" step="1" required value="5">
                        </div>
                    </div>

                    {{-- SUBMIT --}}
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <button class="btn btn-success px-5 w-100">Leave Review</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>

@if (session('leave-review-error'))
    <div class="alert alert-danger card-body text-center mt-4 ">
        {{ session('leave-review-error') }}
    </div>
@endif

@if (session('leave-review-success'))
    <div class="alert alert-success card-body text-center mt-4 ">
        {{ session('leave-review-success') }}
    </div>
@endif
