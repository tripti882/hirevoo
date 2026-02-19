@extends('layouts.app')

@section('title', 'Apply - ' . $job->title)

@section('content')
    <section class="page-title-box">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="text-center text-white">
                        <h3 class="mb-2">Apply for {{ $job->title }}</h3>
                        <p class="mb-0 opacity-75">{{ $job->user->referrerProfile?->company_name ?? 'Company' }}</p>
                        <div class="page-next mt-3">
                            <nav class="d-inline-block" aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50">Home</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('job-openings') }}" class="text-white-50">Job openings</a></li>
                                    <li class="breadcrumb-item active text-white" aria-current="page">Apply</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
                            <h4 class="mb-2">{{ $job->title }}</h4>
                            <p class="text-muted small mb-0">{{ $job->user->referrerProfile?->company_name ?? 'Company' }} @if($job->location) · {{ $job->location }} @endif</p>
                            @if($job->description)
                                <div class="mt-3 text-muted small" style="white-space: pre-wrap;">{{ $job->description }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-lg-5">
                            <h5 class="mb-2">Your application</h5>
                            <p class="text-muted small mb-4">The details below are shown to the employer. Add a resume so they can view your full profile and CV.</p>
                            <form action="{{ route('job-openings.apply.store', $job) }}" method="POST">
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

                                <div class="mb-4">
                                    <h6 class="text-muted small text-uppercase mb-3">Profile details (shown to employer)</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label">Phone</label>
                                            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', auth()->user()->phone ?? '') }}" placeholder="10-digit mobile">
                                        </div>
                                        <div class="col-12">
                                            <label for="headline" class="form-label">Current role / Headline</label>
                                            <input type="text" name="headline" id="headline" class="form-control" value="{{ old('headline', $profile?->headline ?? '') }}" placeholder="e.g. Business Intelligence Analyst at Company Name">
                                        </div>
                                        <div class="col-12">
                                            <label for="education" class="form-label">Education</label>
                                            <input type="text" name="education" id="education" class="form-control" value="{{ old('education', $profile?->education ?? '') }}" placeholder="e.g. BE/B.Tech, College Name">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="experience_years" class="form-label">Experience (years)</label>
                                            <input type="number" name="experience_years" id="experience_years" class="form-control" value="{{ old('experience_years', $profile?->experience_years ?? '') }}" min="0" max="50" placeholder="e.g. 3">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="location" class="form-label">Location</label>
                                            <input type="text" name="location" id="location" class="form-control" value="{{ old('location', $profile?->location ?? '') }}" placeholder="e.g. Gurgaon">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="expected_salary" class="form-label">Expected salary</label>
                                            <input type="text" name="expected_salary" id="expected_salary" class="form-control" value="{{ old('expected_salary', $profile?->expected_salary ?? '') }}" placeholder="e.g. 4.7 Lakhs">
                                        </div>
                                        <div class="col-12">
                                            <label for="skills" class="form-label">Skills (comma-separated)</label>
                                            <textarea name="skills" id="skills" class="form-control" rows="2" placeholder="e.g. Data analytics, Business intelligence, SQL">{{ old('skills', $profile?->skills ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="text-muted small text-uppercase mb-3">Resume & message</h6>
                                    @if($resumes->count() > 0)
                                        <div class="mb-4">
                                            <label for="resume_id" class="form-label fw-medium">Attach a resume (recommended)</label>
                                            <select name="resume_id" id="resume_id" class="form-select">
                                                <option value="">No resume</option>
                                                @foreach($resumes as $r)
                                                    <option value="{{ $r->id }}" {{ old('resume_id', $resumes->first()?->id) == $r->id ? 'selected' : '' }}>
                                                        {{ $r->file_name ?? 'Resume #' . $r->id }} {{ $r->ai_score ? '(' . $r->ai_score . '% ATS)' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="small text-muted mt-1 mb-0">Employers can view and download your CV. Skills are also extracted from your resume.</p>
                                        </div>
                                    @else
                                        <div class="alert alert-light border mb-4">
                                            <p class="mb-2 small">Upload a resume so employers can see your CV and extracted skills.</p>
                                            <a href="{{ route('resume.upload') }}" class="btn btn-sm btn-outline-primary">Upload resume</a>
                                        </div>
                                    @endif
                                    <div class="mb-4">
                                        <label for="cover_message" class="form-label fw-medium">Cover message (optional)</label>
                                        <textarea name="cover_message" id="cover_message" class="form-control" rows="4" placeholder="Why are you a good fit for this role?">{{ old('cover_message') }}</textarea>
                                        <p class="small text-muted mt-1 mb-0">Max 2000 characters.</p>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary">Submit application</button>
                                    <a href="{{ route('job-openings') }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
