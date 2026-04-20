<div class="container my-4">
    @php
        function getColorForReportStatus($statusName)
        {
            return match ($statusName) {
                'in_progress' => 'bg-warning text-dark',
                'completed' => 'bg-success',
                default => 'bg-secondary'
            };
        }
    @endphp

    <div class="row justify-content-center">
        <div class="col-12">
            @if ($reports->count() === 0)
                <div class="alert alert-info text-center">
                    You have not submitted any reports yet.
                </div>
            @endif

            @foreach ($reports as $report)
                <div class="card shadow-sm mb-4 border border-secondary bg-dark text-light">

                    {{-- Header --}}
                    <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center">

                        <h5 class="mt-2 mb-2 fw-semibold">
                            <a href="{{ route('report.show', $report) }}" class="text-decoration-none text-light">
                                {{ $report->title }}
                            </a>
                        </h5>

                        <div class="d-flex gap-2">
                            <span class="badge bg-primary p-2">
                                {{ $report->created_at->format('Y-m-d H:i') }}
                            </span>

                            <span class="badge {{ getColorForReportStatus($report->status->name) }} p-2">
                                {{ ucfirst(str_replace('_', ' ', $report->status->name)) }}
                            </span>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="card-body">
                        <b>Description:</b>
                        <p class="mb-3">
                            {{ $report->description }}
                        </p>

                        @if ($report->order)
                            <b>Related order:</b>
                            <p class="mt-1 mb-2">
                                <a href="{{ route('order.show-order', $report->order) }}"
                                   class="text-info text-decoration-none">
                                    {{ $report->order->title }}
                                </a>
                            </p>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="card-footer bg-dark border-secondary d-flex justify-content-between align-items-center">

                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge bg-light text-dark p-2">
                                <a href="{{ route('public-profile.overview', $report->reporter) }}" class="text-decoration-none text-dark">
                                    👤 {{ $report->reporter->name }}
                                </a>
                            </span>

                            <span class="badge bg-info text-dark p-2">
                                <a href ="{{ route('report.show', $report) }}" class="text-decoration-none text-dark">
                                    Report ID: {{ $report->id }}
                                </a>
                            </span>
                        </div>

                        <a href="{{ route('report.show', $report) }}"
                           class="btn btn-info btn-sm">
                            View Report →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-4 text-center">
        {{ $reports->links('vendor.pagination.bootstrap-5-dark') }}
    </div>
</div>