@extends('main')

@section('content')
    <div class="container">
        <div class="row">
            <div class="row justify-content-center mb-4">
                <div class="col-12 ">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info-subtle text-center">
                            <h5 class="mb-0 text-white ">Create an order</h5>
                        </div>
                        <div class="card-body text-center">
                            {{-- Order creation form placeholder --}}
                            <form method="POST" action="{{ route('order.create-order') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="container">
                                    {{-- CATEGORY --}}
                                    <div class="row mt-3">
                                        <div class="col-3 text-end mt-1 fw-bold">Category:</div>
                                        <div class="col-9">
                                            <select class="form-select text-center" name="sub_category_id" required>
                                                @foreach ($subCategories as $subCategory)
                                                    <option value="{{ $subCategory->id }}">
                                                        {{ $subCategory->name }} — {{ $subCategory->mainOrderCategory->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- TITLE --}}
                                    <div class="row mt-3">
                                        <div class="col-3 text-end mt-1 fw-bold">Title:</div>
                                        <div class="col-9">
                                            <input type="text" name="title" class="form-control text-center" required
                                                value="{{ old('title') }}" maxlength="125" />
                                        </div>
                                    </div>

                                    {{-- SHORT DESCRIPTION --}}
                                    <div class="row mt-3">
                                        <div class="col-3 text-end mt-1 fw-bold">Short description:</div>
                                        <div class="col-9">
                                            <textarea name="short_description" class="form-control" rows="5" maxlength="250" required placeholder="Enter short description for your task…">{{ old('short_description') }}</textarea>
                                        </div>
                                    </div>

                                    {{-- REQUIRMENT SKILLS --}}
                                    <div class="row mt-3">
                                        <div class="col-3 text-end mt-1 fw-bold">Requirment skills:</div>
                                        <div class="col-9">
                                            <textarea name="requirement_skills" class="form-control" rows="5" maxlength="250" required placeholder="Enter all the skills that a potential executor should have to complete your task…">{{ old('requirment_skills') }}</textarea>
                                        </div>
                                    </div>

                                    {{-- FULL DESCRIPTION --}}
                                    <div class="row mt-3">
                                        <div class="col-3 text-end mt-1 fw-bold">Full description:</div>
                                        <div class="col-9">
                                            <textarea name="full_description" class="form-control" rows="10" maxlength="5000" required placeholder="Enter full description for your task…">{{ old('full_description') }}</textarea>
                                        </div>
                                    </div>

                                    {{-- BUDGET --}}
                                    <div class="row mt-3">
                                        <div class="col-3 text-end mt-1 fw-bold">Budget (usd):</div>
                                        <div class="col-9">
                                            <input type="number" name="budget" class="form-control text-center"
                                                min="5" step="0.01" required value="5">
                                        </div>
                                    </div>

                                    {{-- DEADLINE --}}
                                    <div class="row mt-3">
                                        <div class="col-3 text-end mt-1 fw-bold">Deadline (days):</div>
                                        <div class="col-9">
                                            <input type="numeric" step="1" name="deadline_in_days"
                                                class="form-control text-center" required value="1" min="1">
                                        </div>
                                    </div>

                                    {{-- FILE UPLOAD --}}
                                    <div class="row mt-3">
                                        <div class="col-3 text-end mt-1 fw-bold">Select attachments:</div>
                                        <div class="col-9">
                                            <input type="file" id="attachments" name="attachments[]" class="form-control"
                                                multiple
                                                accept=".png, .jpg, .jpeg, .pdf, .doc, .docx, .csv, .xls, .xlsx, .txt">
                                        </div>
                                    </div>

                                    {{-- SUBMIT --}}
                                    <div class="row mt-4">
                                        <div class="col-12 text-center">
                                            <button class="btn btn-success px-5 w-100">
                                                Create order
                                            </button>
                                        </div>
                                    </div>

                                    @if (session('error'))
                                        <div class="alert alert-danger mt-4">
                                            {{ session('error') }}
                                        </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
