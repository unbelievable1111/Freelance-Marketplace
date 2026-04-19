@extends('main')

@section('content')
<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Features</h1>
        <p class="text-muted">
            Everything you need to work efficiently and securely on our platform.
        </p>
    </div>

    <div class="row g-4">

        <!-- Feature 1 -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 p-4">
                <h4>Simple Order Creation</h4>
                <p class="text-muted">
                    Create orders easily in just a few clicks. Describe your requirements,
                    set your budget, and start receiving proposals.
                </p>
            </div>
        </div>

        <!-- Feature 2 -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 p-4">
                <h4>Real-Time Chat</h4>
                <p class="text-muted">
                    Communicate instantly with customers and executors through built-in chat.
                    Stay connected and keep everything in one place.
                </p>
            </div>
        </div>

        <!-- Feature 3 -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 p-4">
                <h4>Smart Notifications</h4>
                <p class="text-muted">
                    Get notified about important updates, new messages, and order changes
                    so you never miss anything important.
                </p>
            </div>
        </div>

        <!-- Feature 4 -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 p-4">
                <h4>Secure Transactions</h4>
                <p class="text-muted">
                    All payments are handled securely. You only pay when the work is completed,
                    ensuring trust between both sides.
                </p>
            </div>
        </div>

        <!-- Feature 5 -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 p-4">
                <h4>User Profiles</h4>
                <p class="text-muted">
                    Manage your profile, track your activity, and build your reputation
                    with reviews and completed orders.
                </p>
            </div>
        </div>

        <!-- Feature 6 -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 p-4">
                <h4>Order Management</h4>
                <p class="text-muted">
                    Easily track your orders, proposals, and progress from one dashboard.
                    Stay organized and in control.
                </p>
            </div>
        </div>

    </div>
</div>
@endsection