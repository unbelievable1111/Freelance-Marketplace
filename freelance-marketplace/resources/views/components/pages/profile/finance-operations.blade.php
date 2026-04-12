@extends('components.pages.profile.menu')

@section('profile-content')
    <div class="container mt-4">
        {{-- Form for showing and updating user's avatar --}}
        <div class="row justify-content-center mb-4">
            <div class="col-12" style="min-width: 700px">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-center">
                        <h5 class="mb-0 text-white">Your balance</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="container">
                            <h3>
                                <span class="text-success">{{ Auth()->user()->balance->amount }} USD</span>
                                @if (Auth()->user()->balance->escrowed_amount > 0)
                                    <span class="text-warning"> ({{ Auth()->user()->balance->escrowed_amount }} USD ESCROWED)</span>
                                @endif
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        {{-- Form for Deposit --}}
        <div class="row justify-content-center mb-4">
            <div class="col-12" style="min-width: 700px">
                <div class="card shadow-sm">
                    <div class="card-header text-center bg-success">
                        <h5 class="mb-0 text-white">Deposit</h5>
                    </div>
                    <div class="card-body">
                        <div class="container">
                            @if (count(Auth()->user()->bankAccounts) == 0)
                                <div class="row text-center">
                                    <h5>There are no cards. You have to add one. <a
                                            href="{{ route('profile.bank-accounts') }}">Open page for adding cards.</a></h5>
                                </div>
                            @else
                                <div class="mb-3">
                                    <form method="POST" action="{{ route('profile.transactions.deposit') }}">
                                        @csrf
                                        <small class="text-muted">Bank's card:</small>
                                        <br>
                                        <select class="form-select w-100 text-center mt-2" name="bank_account_id">
                                            @foreach (Auth()->user()->bankAccounts as $bankAccount)
                                                <option value="{{ $bankAccount->id }}">{{ $bankAccount->name }} -
                                                    {{ chunk_split($bankAccount->card_number, 4, ' ') }}</option>
                                            @endforeach
                                        </select>
                                        <br>
                                        <small class="text-muted">Amount:</small>
                                        <br>
                                        <input class="form-control w-100 mt-2" name="amount" type="number" value="0.00"
                                            step="0.01" min="0.01" max="10000"
                                            placeholder="Enter the deposit amount" />
                                        <br>
                                        <button class="w-100 btn btn-success">Deposit</button>
                                    </form>
                                </div>
                            @endif

                            @if (session('deposit_success'))
                                <div class="alert alert-success mt-3">
                                    {{ session('deposit_success') }}
                                </div>
                            @endif

                            @if (session('deposit_failure'))
                                <div class="alert alert-danger mt-3">
                                    {{ session('deposit_failure') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form for Withdraw --}}
        <div class="row justify-content-center mb-4">
            <div class="col-12" style="min-width: 700px">
                <div class="card shadow-sm">
                    <div class="card-header text-center bg-danger">
                        <h5 class="mb-0 text-white">Withdraw</h5>
                    </div>
                    <div class="card-body">
                        <div class="container">
                            @if (count(Auth()->user()->bankAccounts) == 0)
                                <div class="row text-center">
                                    <h5>There are no cards. You have to add one. <a
                                            href="{{ route('profile.bank-accounts') }}">Open page for adding cards.</a></h5>
                                </div>
                            @else
                                <div class="mb-3">
                                    <form method="POST" action="{{ route('profile.transactions.withdraw') }}">
                                        @csrf
                                        <small class="text-muted">Bank's card:</small>
                                        <br>
                                        <select class="form-select w-100 text-center mt-2" name="bank_account_id">
                                            @foreach (Auth()->user()->bankAccounts as $bankAccount)
                                                <option value="{{ $bankAccount->id }}">{{ $bankAccount->name }} -
                                                    {{ chunk_split($bankAccount->card_number, 4, ' ') }}</option>
                                            @endforeach
                                        </select>
                                        <br>
                                        <small class="text-muted">Amount:</small>
                                        <br>
                                        <input class="form-control w-100 mt-2" name="amount" type="number" value="0.00"
                                            step="0.01" min="0.01" max="10000"
                                            placeholder="Enter the deposit amount" />
                                        <br>
                                        <button class="w-100 btn btn-danger">Withdraw</button>
                                    </form>
                                </div>
                            @endif

                            @if (session('withdraw_success'))
                                <div class="alert alert-success mt-3">
                                    {{ session('withdraw_success') }}
                                </div>
                            @endif

                            @if (session('withdraw_failure'))
                                <div class="alert alert-danger mt-3">
                                    {{ session('withdraw_failure') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection