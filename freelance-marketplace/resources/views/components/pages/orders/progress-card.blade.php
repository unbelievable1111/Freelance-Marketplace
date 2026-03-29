<div class="row justify-content-center  mt-4">
    <div class="col-12">
        <div class="card shadow-sm mb-4 border border-secondary bg-dark text-light">
            {{-- Header --}}
            <div class="card-header bg-dark border-secondary d-flex align-items-center">
                <h5 class="mt-2 mb-2 fw-semibold text-light">Order progress</h5>
            </div>

            {{-- Body --}}
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">
                        <b>Executor: </b>
                        {{ $order->executor ? $order->executor->name : 'Not assigned yet' }}
                    </label>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        <b>Proposal: </b>
                        {{ $submittedApprove ? $submittedApprove->comment : 'No comment provided yet.' }}
                    </label>

                    <hr>

                    {{-- Comments --}}
                    @if ($comments->isEmpty())
                        <div class="alert alert-info mt-4 text-center" role="alert">
                            There are no comments yet!
                        </div>
                    @else
                        <div class="mt-4">
                            @forelse($comments as $comment)
                                <div class="card mb-3 shadow-sm border-1 bg-dark text-light rounded-3">
                                    <div class="card-body p-3">
                                        {{-- Header --}}
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <div class="fw-semibold">
                                                    {{ $comment->user->name }}
                                                    <span
                                                        class="badge {{ $comment->user->UserRole->name == 'customer' ? 'bg-danger' : 'bg-success' }} ms-2 p-2">
                                                        {{ $comment->user->UserRole->name }}
                                                    </span>
                                                </div>
                                            </div>

                                            <small class="text-muted">
                                                {{ $comment->created_at->diffForHumans() }}
                                            </small>
                                        </div>

                                        {{-- Divider --}}
                                        <hr class="border-secondary my-2">

                                        {{-- Comment text --}}
                                        <p class="mb-2" style="text-align: justify; line-height: 1.6;">
                                            {{ $comment->value }}
                                        </p>

                                        {{-- Attachments --}}
                                        @if (!$comment->fileAttachments->isEmpty())
                                            <div class="mt-3">
                                                <div class="text-muted small mb-2">📎 Attachments</div>

                                                <div class="list-group list-group-flush">
                                                    @foreach ($comment->fileAttachments as $attachment)
                                                        <a href="{{ asset('storage/public_order_attachments/' . $attachment->stored_filename) }}"
                                                            target="_blank"
                                                            class="list-group-item list-group-item-action bg-dark text-light border-secondary rounded mb-1">
                                                            <i class="bi bi-file-earmark-text me-2"></i>
                                                            {{ $attachment->original_filename }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="alert alert-info">
                                    There are no comments yet!
                                </div>
                            @endforelse
                        </div>
                    @endif

                    {{-- Pagination info --}}
                    @if ($comments->hasPages())
                        <div class="card-footer bg-dark border-secondary d-flex justify-content-center p-3">
                            <div class="bg-dark border-secondary">
                                <div class="d-flex justify-content-center">
                                    {{ $comments->appends(request()->except('p_comments'))->links('vendor.pagination.bootstrap-5-dark') }}
                                </div>
                            </div>
                        </div>
                        <hr>
                    @endif

                    {{-- Form for leaving a comment --}}
                    <form action="{{ route('order.leave-comment', $order) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <textarea name="value" class="form-control bg-dark text-light border-secondary" rows="3"
                            placeholder="Leave a comment for your proposal..." required></textarea>

                        {{-- FILE UPLOAD --}}
                        <div class="row mt-3">
                            <div class="col-2 text-end mt-1 fw-bold">Select attachments:</div>
                            <div class="col-10">
                                <input type="file" id="attachments" name="attachments[]" class="form-control"
                                    multiple accept=".png, .jpg, .jpeg, .pdf, .doc, .docx, .csv, .xls, .xlsx, .txt">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-sm mt-3 w-100 p-2">Leave a comment</button>
                    </form>

                    @if (session('leaveCommentError'))
                        <div class="alert alert-danger mt-4">
                            {{ session('leaveCommentError') }}
                        </div>
                    @endif

                    @if (session('leaveCommentSuccess'))
                        <div class="alert alert-success mt-4">
                            {{ session('leaveCommentSuccess') }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            <div class="card-footer bg-dark border-secondary d-flex justify-content-between align-items-center">
                <div class="d-flex gap-3 flex-grow-1">
                    {{-- DEADLINE --}}
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-light">
                            <b>Deadline:</b>
                            {{ $order->deadline_date ? $order->deadline_date->format('Y-m-d H:i') : 'Not set' }}
                        </span>

                        @if ($order->deadline_date && $order->deadline_date->isPast())
                            <span class="text-danger">(Expired)</span>
                        @else
                            @if ($order->isInProgress() || $order->isExpired())
                                <span class="text-success">
                                    (Remaining: {{ now()->diffForHumans($order->deadline_date, true) }})
                                </span>
                            @endif
                        @endif

                        @if ($order->canExtendDeadlineBy(auth()->user()))
                            <form method="POST" action="{{ route('order.extend-deadline', $order) }}"
                                class="d-flex align-items-center gap-2 flex-wrap">
                                @csrf
                                <input type="number" step="1" name="additional_days"
                                    class="form-control text-center w-auto" required value="1" min="1"
                                    style="max-width: 80px;">
                                <button class="btn btn-primary p-2">Extend Deadline</button>
                            </form>
                        @endif

                        @if ($order->canBeCompletedBy(auth()->user()))
                            <form action="{{ route('order.complete-order', $order) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-success w-100 p-2"> Complete the order </button>
                            </form>
                        @endif

                        @if ($order->canBeCanceledBy(auth()->user()))
                            <form action="{{ route('order.cancel-order', $order) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="btn btn-danger w-100 p-2">
                                    Cancel the order
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
