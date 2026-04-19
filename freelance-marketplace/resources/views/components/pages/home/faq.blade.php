@extends('main')

@section('content')
<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">FAQs</h1>
        <p class="text-muted">
            Answers to the most common questions about our platform.
        </p>
    </div>

    <div class="row g-4">

        <!-- Question 1 -->
        <div class="col-md-6">
            <div class="card bg-dark text-light border shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">How does the platform work?</h5>
                    <p class="card-text text-muted">
                        Customers create orders by describing their needs, and executors submit proposals.
                        Once both sides agree, the work begins and is completed through the platform.
                    </p>
                </div>
            </div>
        </div>

        <!-- Question 2 -->
        <div class="col-md-6">
            <div class="card bg-dark text-light border shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Are there any subscription fees?</h5>
                    <p class="card-text text-muted">
                        No, there are no subscription fees. You can use the platform for free.
                    </p>
                </div>
            </div>
        </div>

        <!-- Question 3 -->
        <div class="col-md-6">
            <div class="card bg-dark text-light border shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">What fees do you charge?</h5>
                    <p class="card-text text-muted">
                        We charge a 5% commission only after an order is successfully completed.
                        Additional fees may apply depending on the payment provider.
                    </p>
                </div>
            </div>
        </div>

        <!-- Question 4 -->
        <div class="col-md-6">
            <div class="card bg-dark text-light border shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">How do payments work?</h5>
                    <p class="card-text text-muted">
                        Payments are processed securely through supported payment systems.
                        Funds are transferred only after the order is completed.
                    </p>
                </div>
            </div>
        </div>

        <!-- Question 5 -->
        <div class="col-md-6">
            <div class="card bg-dark text-light border shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Can I communicate with other users?</h5>
                    <p class="card-text text-muted">
                        Yes, you can use the built-in chat system to communicate in real time
                        with customers and executors.
                    </p>
                </div>
            </div>
        </div>

        <!-- Question 6 -->
        <div class="col-md-6">
            <div class="card bg-dark text-light border shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Is my data secure?</h5>
                    <p class="card-text text-muted">
                        Yes, we take security seriously and use modern technologies to protect your data
                        and transactions.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection