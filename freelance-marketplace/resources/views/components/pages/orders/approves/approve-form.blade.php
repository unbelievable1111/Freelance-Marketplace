<form action="{{ route('order.approve', $order) }}" method="POST">
    @csrf
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm mb-4 mt-4 border border-secondary bg-dark text-light">
                {{-- Header --}}
                <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center">
                    <h5 class="mt-2 mb-2 fw-semibold text-light">Proposal Form</h5>
                </div>

                {{-- Body --}}
                <div class="card-body">
                    <b>Proposal description:</b>
                    <textarea name="proposal_description" class="form-control mt-2" rows="5" maxlength="1000" 
                    required placeholder="Enter short description about your proposal for this task…">{{ old('proposal_description') }}</textarea>

                    <div class="d-flex flex-column flex-grow-1 mt-2">
                        <label class="form-label text-light mb-1"><b>Deadline (days):</b></label>
                        <input type="number" name="deadline_in_days"
                            class="form-control bg-dark text-light border-secondary text-center"
                            value="{{ old('deadline_in_days', $order->deadline_in_days) }}" 
                            min="1"
                            step="1" 
                            required
                        >
                    </div>

                    <div class="d-flex flex-column flex-grow-1 mt-2">
                        <label class="form-label text-light mb-1"><b>Budget (USD):</b></label>
                        <input type="number" name="proposal_budget"
                            class="form-control bg-dark text-light border-secondary text-center"
                            value="{{ old('proposal_budget', $order->budget) }}" min="5" step="0.01" required>
                    </div>

                    <button type="submit" class="btn btn-success btn-sm mt-3 w-100 p-2">Submit Proposal</button>
                </div>
            </div>
        </div>
    </div>
</form>

@if (session('error-make-approve'))
    <div class="alert alert-danger mt-4">
        {{ session('error-make-approve') }}
    </div>
@endif