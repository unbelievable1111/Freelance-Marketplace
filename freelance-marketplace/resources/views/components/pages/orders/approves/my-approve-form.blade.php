<div class="alert alert-info mt-4" role="alert">
    You have already submitted a proposal for this order. Please wait for the client to review it, or modify your
    proposal.
</div>

<form action="{{ route('order.update-approve', $order) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm mb-4 mt-4 border border-secondary bg-dark text-light">
                {{-- Header --}}
                <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center">
                    <h5 class="mt-2 mb-2 fw-semibold text-light">Your Proposal</h5>
                </div>

                {{-- Body --}}
                <div class="card-body">
                    <b>Proposal description:</b>
                    <textarea name="proposal_description" class="form-control mt-2" rows="5" maxlength="1000" required
                        placeholder="Enter short description about your proposal for this task…">{{ $myApprove ? $myApprove->comment : '' }}</textarea>

                    <div class="d-flex flex-column flex-grow-1 mt-2">
                        <label class="form-label text-light mb-1"><b>Deadline (days):</b></label>
                        <input type="number" name="deadline_in_days"
                            class="form-control bg-dark text-light border-secondary text-center"
                            value="{{ $myApprove ? $myApprove->proposed_deadline_in_days : '' }}" min="1"
                            step="1" required>
                    </div>

                    <div class="d-flex flex-column flex-grow-1 mt-2">
                        <label class="form-label text-light mb-1"><b>Budget (USD):</b></label>
                        <input type="number" name="proposal_budget"
                            class="form-control bg-dark text-light border-secondary text-center"
                            value="{{ $myApprove ? $myApprove->proposed_budget : '' }}" min="5" step="0.01"
                            required>
                    </div>

                    <button type="submit" class="btn btn-warning btn-sm w-100 p-2 mt-3">Edit Proposal</button>
                </div>
            </div>
        </div>
    </div>
</form>

<form action="{{ route('order.cancel-approve', $order) }}" method="POST">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger btn-sm w-100 p-2 mt-1">Cancel Proposal</button>
</form>
