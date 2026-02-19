@extends('layouts.app')

@section('title', 'Job openings')

@section('content')
    <section class="page-title-box">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="text-center text-white">
                        <h3 class="mb-4">Job openings</h3>
                        <div class="page-next">
                            <nav class="d-inline-block" aria-label="breadcrumb text-center">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Job openings</li>
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
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <p class="text-muted mb-4">Browse job posts from verified employers. Sign in as a candidate to apply.</p>

            <div class="row">
                @forelse($jobs as $job)
                    <div class="col-lg-4 col-md-6 mt-4 pt-2">
                        <div class="card border shadow-none rounded-3 mb-3 h-100">
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="mb-2">{{ $job->title }}</h5>
                                <p class="text-muted small mb-2">
                                    {{ $job->user->referrerProfile?->company_name ?? 'Company' }}
                                    @if($job->location)
                                        · {{ $job->location }}
                                    @endif
                                </p>
                                <p class="text-muted mb-0 fs-14 flex-grow-1">{{ Str::limit($job->description, 100) ?: '—' }}</p>
                                <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                                    @if(in_array($job->id, $appliedIds ?? []))
                                        <span class="badge bg-success">Applied</span>
                                    @else
                                        <a href="{{ route('job-openings.apply', $job) }}" class="btn btn-primary btn-sm">Apply</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border shadow-none rounded-3">
                            <div class="card-body p-5 text-center">
                                <p class="text-muted mb-0">No job openings at the moment. Check back later.</p>
                                <a href="{{ route('home') }}" class="btn btn-primary mt-3">Back to Home</a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($jobs->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $jobs->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
