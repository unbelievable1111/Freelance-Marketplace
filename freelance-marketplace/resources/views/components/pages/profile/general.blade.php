@extends('components.pages.profile.menu')

@section('profile-content')
    <div class="container mt-4">
        {{-- Form for showing and updating user's avatar --}}
        <div class="row justify-content-center mb-4">
            <div class="col-12 ">
                <div class="card shadow-sm">
                    <div class="card-header bg-info-subtle text-center">
                        <h5 class="mb-0 text-white">Avatar</h5>
                    </div>
                    <div class="card-body text-center">
                        <!-- Current user's avatar -->
                        <div class="d-flex justify-content-center mb-3">
                            <img src="{{ auth()->user()->avatar }}" alt="User's avatar"  class="rounded-circle"  style="width: 200px; height: 200px; object-fit: cover;">
                        </div>

                        <!-- The form for changing the avatar -->
                        <form action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <input class="form-control" type="file" name="avatar" accept="image/*" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Change Avatar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- General info --}}
        <div class="row justify-content-center mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-center">
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
                            <div class="fs-5 text-success">{{auth()->user()->Balance->amount}} USD</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Change password --}}
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-sm border-warning">
                    <div class="card-header bg-warning-subtle text-center">
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
                            <button type="submit" class="btn btn-warning w-100">
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