@extends('main')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                @if ($notifications->isEmpty())
                    <div class="alert alert-info text-center mt-5">
                        No notifications yet.
                    </div>
                @endif

                @foreach ($notifications as $notification)
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title ">
                                {{ $notification->notificationType->description }}
                                
                                @if (!$notification->is_read)
                                    <span class="badge bg-primary m-1">New</span>
                                @endif

                            </h5>

                            <p class="card-text">
                                {!! $notification->message !!}
                            </p>

                            <p class="card-text">
                                <small class="text-muted">
                                    {{ $notification->created_at }}
                                </small>
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-4 text-center">
            {{ $notifications->appends(request()->except('p'))->links('vendor.pagination.bootstrap-5-dark') }}
        </div>
    </div>
@endsection