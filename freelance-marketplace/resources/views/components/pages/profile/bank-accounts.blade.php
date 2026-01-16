@extends('components.pages.profile.menu')

@section('profile-content')
    <div class="container mt-4">
        {{-- Form for showing and updating user's avatar --}}
        <div class="row justify-content-center mb-4">
            <div class="col-12" style="min-width: 700px">
                <div class="card shadow-sm">
                    <div class="card-header bg-info-subtle text-center">
                        <h5 class="mb-0 text-white">Your bank accounts</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="container">
                            @if (count(Auth()->user()->bankAccounts) == 0)
                                <h5>There are no cards. You have to add one.</h5>
                            @endif

                            @foreach (Auth()->user()->bankAccounts as $BankAccount)
                                <div class="row justify-content-center mt-3  px-30">
                                    <div class="bank-card-wrapper position-relative" style="min-width: 600px">
                                        <img src="{{ asset('storage/images/bank_card_template.png') }}" class="img-fluid" alt="">

                                        {{-- Текст --}}
                                        <div
                                            class="position-absolute top-58 start-50 translate-middle text-white text-center">
                                            <h3 class="shadow-lg text-nowrap">
                                                {{ chunk_split($BankAccount->card_number, 4, ' ') }}</h3>
                                            <h5>{{ $BankAccount->name }}</h5>
                                        </div>

                                        {{-- Кнопка --}}
                                        <form method="POST"
                                            action="{{ route('profile.bank-accounts.delete-card', $BankAccount->id) }}">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger position-absolute bottom-0 end-0 m-4">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success mt-3">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('failure'))
                            <div class="alert alert-danger mt-3">
                                {{ session('failure') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 mt-2" style="min-width: 700px">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning-subtle text-center">
                        <h5 class="mb-0 text-white">Add new card</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="container">
                            <form method="POST" action="{{ route('profile.bank-accounts.create-card') }}">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Card name</label>
                                    <input type="text" name="name" class="form-control" required maxlength="64">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Card number</label>
                                    <input type="text" name="card_number" class="form-control" inputmode="numeric"
                                        pattern="[0-9]{16}" maxlength="16" placeholder="1234 1234 1234 1234" required>
                                    <small class="text-muted">16 digits, numbers only</small>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    Add card
                                </button>
                            </form>

                            @if (session('creation-card-success'))
                                <div class="alert alert-success mt-3">
                                    {{ session('creation-card-success') }}
                                </div>
                            @endif

                            @if (session('creation-card-failure'))
                                <div class="alert alert-danger mt-3">
                                    {{ session('creation-card-failure') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection