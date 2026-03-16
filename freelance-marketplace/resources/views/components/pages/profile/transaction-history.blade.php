@extends('components.pages.profile.menu')

@section('profile-content')
    {{-- Transaction History Table --}}
    <table class="table table-dark text-center">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Order</th>
                <th scope="col">Type</th>
                <th scope="col">Amount</th>
                <th scope="col">Card Number</th>
                <th scope="col">Exucutor</th>
                <th scope="col">Related User</th>
                <th scope="col">
                    <select class="p-0 bg-dark text-white border-0 text-center" aria-label="Sort by date" style="appearance: none; -webkit-appearance: none; -moz-appearance: none;">
                        <option value="asc" {{ $order === 'asc' ? 'selected' : '' }} onclick="window.location='{{ route('profile.transactions.history', ['order' => 'asc', 'page' => 1]) }}'">
                            Date (Ascending)
                        </option>
                        <option value="desc" {{ $order === 'desc' ? 'selected' : '' }} onclick="window.location='{{ route('profile.transactions.history', ['order' => 'desc', 'page' => 1]) }}'">
                            Date (Descending)
                        </option>
                    </select>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
                @php
                    if (strtolower($transaction->transactionType->name) === 'deposit') 
                    {
                        $amountClass = 'text-success';
                    }
                    elseif (strtolower($transaction->transactionType->name) === 'withdraw') 
                    {
                        $amountClass = 'text-danger';
                    }
                    elseif (strtolower($transaction->transactionType->name) === 'transfer') 
                    {
                        if ($transaction->user_id === auth()->user()->id) {
                            $amountClass = 'text-danger';
                        }

                        if ($transaction->related_user_id === auth()->user()->id) {
                            $amountClass = 'text-success';
                        }
                    }
                    elseif (strtolower($transaction->transactionType->name) === 'escrow') 
                    {
                        if ($transaction->user_id === auth()->user()->id) {
                            $amountClass = 'text-warning';
                        }

                        if ($transaction->related_user_id === auth()->user()->id) {
                            $amountClass = 'text-success';
                        }
                    }
                    elseif (strtolower($transaction->transactionType->name) === 'refund_escrow') {
                        if ($transaction->user_id === auth()->user()->id) {
                            $amountClass = 'text-success';
                        }

                        if ($transaction->related_user_id === auth()->user()->id) {
                            $amountClass = 'text-danger';
                        }
                    }
                    else
                    {
                        $amountClass = '';
                    }
                @endphp

                <tr class="text-center">
                    <td class="{{ $amountClass }}">{{ $transaction->id }}</td>

                    @if ($transaction->order)
                        <td class="{{ $amountClass }}"><a class="{{ $amountClass }} text-decoration-none" href="{{ route('order.show-order', $transaction->order) }}">#{{ $transaction->order_id }}</a></td>
                    @else
                        <td class="{{ $amountClass }}">-</td>
                    @endif

                    <td class="{{ $amountClass }}">{{ $transaction->transactionType->name }}</td>
                    <td class="{{ $amountClass }}">{{ $transaction->amount }}</td>
                    <td class="{{ $amountClass }}">{{ $transaction->bankAccount ? chunk_split($transaction->bankAccount->card_number, 4, ' ') : '-' }}</td>
                    <td class="{{ $amountClass }}">{{ $transaction->user->name . ' (' . $transaction->user->id . ')' }}</td>
                    <td class="{{ $amountClass }}">{{ $transaction->ReletedUser ? $transaction->ReletedUser->name . ' (' . $transaction->ReletedUser->id . ')' : $transaction->user->name . ' (' . $transaction->user->id . ')' }}</td>
                    <td class="{{ $amountClass }}">{{ $transaction->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{-- Pagination Links --}}
    <div class="d-flex justify-content-center">
        <button class="btn btn-secondary me-2"
            onclick="window.location='{{ route('profile.transactions.history', ['order' => $order, 'page' => $page - 1]) }}'"
            @if ($page == 1) disabled @endif>
            Previous
        </button>
        <span class="align-self-center text-white">Page {{ $page }} of {{$totalPages}}</span>
        <button class="btn btn-secondary ms-2"
            onclick="window.location='{{ route('profile.transactions.history', ['order' => $order, 'page' => $page + 1]) }}'"
            @if ($transactions->count() < \App\Http\Controllers\TransactionController::TRANSACTIONS_PER_PAGE) disabled @endif>
            Next
        </button>
    </div>
@endsection