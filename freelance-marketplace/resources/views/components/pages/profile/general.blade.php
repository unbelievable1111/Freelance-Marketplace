@extends('components.pages.profile.menu')

@section('profile-content')
    <div class="container mt-4">

        {{-- General info --}}
        <div class="row justify-content-center mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-success">
                        <h5 class="mb-0 text-white">Profile Information</h5>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">Name</small>
                            <div class="fs-5">{{ auth()->user()->name }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">Email</small>
                            <div class="fs-5">{{ auth()->user()->email }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">Role</small>
                            <div class="fs-5">{{ auth()->user()->UserRole->name }}</div>
                        </div>

                        <div>
                            <small class="text-muted">Balance</small>
                            <div class="fs-5 text-success">0.00 USD</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Change password --}}
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-sm border-warning">
                    <div class="card-header bg-warning-subtle">
                        <h5 class="mb-0 text-white">Change Password</h5>
                    </div>
                    <div class="card-body">

                        <form method="POST" action="{{ route('profile.password.update') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Current password</label>
                                <input type="password" name="current_password"
                                    class="form-control @error('current_password') is-invalid @enderror">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New password</label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm new password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-warning">
                                Update password
                            </button>
                        </form>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
