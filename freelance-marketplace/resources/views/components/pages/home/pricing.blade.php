@extends('main')

@section('content')
<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Pricing</h1>
        <p class="text-muted">
            Simple and transparent pricing with no hidden fees.
        </p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 p-4">
                <div class="card-body">

                    <h4 class="mb-3">No Subscription Fees</h4>
                    <p class="text-muted">
                        You can use our platform completely free of charge. There are no monthly subscriptions,
                        no hidden costs, and no upfront payments.
                    </p>

                    <hr>

                    <h4 class="mb-3">Service Fee</h4>
                    <p class="text-muted">
                        We only charge a <strong>5% commission</strong> when an order is successfully completed.
                        This ensures you only pay when you get value from the platform.
                    </p>

                    <hr>

                    <h4 class="mb-3">Payment Processing Fees</h4>
                    <p class="text-muted">
                        Depending on the payment method you choose, additional fees may be applied by the payment provider.
                        These fees are determined by the payment system itself and are outside of our control.
                    </p>

                    <hr>

                    <h4 class="mb-3">Full Transparency</h4>
                    <p class="text-muted">
                        We believe in clear and honest pricing. All applicable fees will always be shown before you confirm a transaction.
                    </p>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection