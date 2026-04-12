{{-- Reviews --}}
{{-- Container for reviews --}}
<div class="w-100">
    <div class="row justify-content-center mb-4 ">
        <div class="col-12">
            <div class="card shadow-sm">

                <div class="card-header bg-primary text-center">
                    <h5 class="mb-0 text-white">Reviews</h5>
                </div>

                <div class="card-body">
                    @if (!$reviews->isEmpty())
                        @foreach ($reviews as $review)
                            <div class="card bg-dark text-white shadow mb-3 w-100">
                                <div class="card-body">

                                    {{-- Header --}}
                                    <div class="d-flex justify-content-between align-items-start flex-wrap">

                                        <div>
                                            <h5 class="mb-1">
                                                <a href="{{ route('public-profile.overview', $review->author) }}" class="text-decoration-none text-light">
                                                    {{ $review->author->name }}
                                                </a>
                                            </h5>

                                            <small class="text-muted">
                                                <a href="{{ route('order.show-order', $review->order) }}" class="text-decoration-none text-muted">
                                                {{ $review->created_at->format('Y-m-d H:i') }}
                                                • Order #{{ $review->order_id }}
                                                
                                                    {{ $review->order->title }}
                                                </a>
                                            </small>
                                        </div>

                                        <div class="mt-2 mt-md-0">
                                            @for ($i = 0; $i < 5; $i++)
                                                <span class="{{ $i < $review->score ? 'text-warning' : 'text-secondary' }}">
                                                    ★
                                                </span>
                                            @endfor
                                        </div>

                                    </div>

                                    <hr class="border-secondary">

                                    {{-- Feedback --}}
                                    <p class="mb-0" style="white-space: pre-line;">
                                        {{ $review->feedback }}
                                    </p>
                                </div>
                            </div>
                        @endforeach

                        {{-- Pagination --}}
                        <div class="mt-4 text-center">
                            {{ $reviews->appends(request()->except('p'))->links('vendor.pagination.bootstrap-5-dark') }}
                        </div>

                    @else
                        <div class="text-center text-muted">
                            There are no reviews yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>