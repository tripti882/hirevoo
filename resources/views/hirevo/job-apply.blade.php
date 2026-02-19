@extends('layouts.app')

@section('title', 'Apply - ' . $jobRole->title)

@section('content')
    <!-- Start page title -->
    <section class="page-title-box">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="text-center text-white">
                        <h3 class="mb-2">Apply for {{ $jobRole->title }}</h3>
                        <p class="mb-0 opacity-75">Submit your application. We'll review and get back to you.</p>
                        <div class="page-next mt-3">
                            <nav class="d-inline-block" aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50">Home</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('job-list') }}" class="text-white-50">Job Goals</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('job-goal.show', $jobRole) }}" class="text-white-50">{{ $jobRole->title }}</a></li>
                                    <li class="breadcrumb-item active text-white" aria-current="page">Apply</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end page title -->

    <div class="position-relative" style="z-index: 1">
        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 1440 250">
                <path fill="" fill-opacity="1" d="M0,192L120,202.7C240,213,480,235,720,234.7C960,235,1200,213,1320,202.7L1440,192L1440,320L1320,320C1200,320,960,320,720,320C480,320,240,320,120,320L0,320Z"></path>
            </svg>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h4 class="mb-2">{{ $jobRole->title }}</h4>
                            @if($jobRole->description)
                                <p class="text-muted mb-0">{{ $jobRole->description }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-lg-5">
                            <h5 class="mb-4">Your application</h5>
                            <form action="{{ route('job-goal.apply.store', $jobRole) }}" method="POST">
                                @csrf
                                @if($errors->any())
                                    <div class="alert alert-danger mb-4">
                                        <ul class="mb-0 list-unstyled">
                                            @foreach($errors->all() as $err)
                                                <li>{{ $err }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if($resumes->count() > 0)
                                    <div class="mb-4">
                                        <label for="resume_id" class="form-label fw-medium">Attach a resume (optional)</label>
                                        <select name="resume_id" id="resume_id" class="form-select">
                                            <option value="">No resume</option>
                                            @foreach($resumes as $r)
                                                <option value="{{ $r->id }}" {{ old('resume_id') == $r->id ? 'selected' : '' }}>
                                                    {{ $r->file_name ?? 'Resume #' . $r->id }} {{ $r->ai_score ? '(' . $r->ai_score . '% ATS)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="small text-muted mt-1 mb-0">We'll use this resume when reviewing your application.</p>
                                    </div>
                                @endif
                                <div class="mb-4">
                                    <label for="cover_message" class="form-label fw-medium">Cover message (optional)</label>
                                    <textarea name="cover_message" id="cover_message" class="form-control" rows="4" placeholder="Why are you a good fit for this role?">{{ old('cover_message') }}</textarea>
                                    <p class="small text-muted mt-1 mb-0">Max 2000 characters.</p>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="uil uil-message me-1"></i> Submit application</button>
                                    <a href="{{ route('job-goal.show', $jobRole) }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
