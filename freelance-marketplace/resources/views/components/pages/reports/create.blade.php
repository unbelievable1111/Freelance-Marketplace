@extends('main')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow border-0">
                {{-- HEADER --}}
                <div class="card-header bg-danger text-white text-center">
                    <h5 class="mb-0">Report Order</h5>
                </div>

                <div class="card-body">

                    {{-- ORDER INFO --}}
                    <div class="card mb-4 border-secondary">
                        <div class="card-header bg-secondary text-white">
                            Order Information
                        </div>

                        <div class="card-body">
                            <h6 class="fw-bold mb-2">
                                {{ $order->title ?? 'No title' }}
                            </h6>

                            <p class="mb-2 text-muted">
                                {{ \Illuminate\Support\Str::limit($order->description, 300) ?? 'No description' }}
                            </p>

                            <div class="d-flex justify-content-between small text-muted">
                                @if(isset($order->budget))
                                    <span><strong>Budget:</strong> ${{ $order->budget }}</span>
                                @endif

                                @if(isset($order->deadline_in_days))
                                    <span><strong>Deadline:</strong> {{ $order->deadline_in_days }} days</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- WARNING --}}
                    <div class="alert alert-warning">
                        Please provide accurate information. False reports may lead to account restrictions.
                    </div>

                    {{-- GLOBAL ERRORS --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- FORM --}}
                    <form method="POST" action="{{ route('report.store', $order->id) }}">
                        @csrf

                        {{-- TITLE --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Report title / reason</label>
                            <input 
                                type="text"
                                name="reason"
                                class="form-control @error('reason') is-invalid @enderror"
                                value="{{ old('reason') }}"
                                maxlength="255"
                                placeholder="e.g. Scam, inappropriate content, fraud..."
                                required
                            >

                            @error('reason')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- DESCRIPTION --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Detailed description</label>
                            <textarea 
                                name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                rows="6"
                                placeholder="Explain the issue in detail..."
                                required
                            >{{ old('description') }}</textarea>

                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- HIDDEN --}}
                        <input type="hidden" name="order_id" value="{{ $order->id }}">

                        {{-- SUBMIT --}}
                        <button class="btn btn-danger w-100">
                            Submit Report
                        </button>

                        {{-- SUCCESS --}}
                        @if (session('success'))
                            <div class="alert alert-success mt-3">
                                {{ session('success') }}
                            </div>
                        @endif

                        {{-- ERROR --}}
                        @if (session('error'))
                            <div class="alert alert-danger mt-3">
                                {{ session('error') }}
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection