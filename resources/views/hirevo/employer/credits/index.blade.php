@extends('layouts.employer')

@section('title', 'Buy Credits')
@section('header_title', 'Credits')

@section('content')
    <div class="card employer-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-4">
                <div class="avatar-sm bg-success-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                    <i class="mdi mdi-coin-outline text-success fs-24"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-600">Available Credits</h5>
                    <p class="text-muted small mb-0">1 credit = 1 job post. Use credits when you create or repost a job.</p>
                </div>
                <div class="ms-auto text-end">
                    <span class="fs-3 fw-700 text-dark">{{ $credits }}</span>
                    <span class="text-muted small d-block">credits left</span>
                </div>
            </div>
            @if($credits < 1)
                <div class="alert alert-warning mb-0">
                    <i class="mdi mdi-alert-circle-outline me-2"></i>You have no credits. Buy credits below to post or repost jobs.
                </div>
            @endif
        </div>
    </div>

    <h6 class="fw-600 mb-3">Credit packages</h6>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card employer-card h-100 border-primary">
                <div class="card-body p-4 text-center">
                    <div class="mb-2"><i class="mdi mdi-coin text-primary" style="font-size: 2rem;"></i></div>
                    <h5 class="fw-600 mb-1">5 Credits</h5>
                    <p class="text-muted small mb-3">5 job posts</p>
                    <a href="{{ route('contact') }}?subject=Buy%205%20credits" class="btn btn-outline-primary btn-sm w-100">Contact to purchase</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card employer-card h-100 border-success">
                <div class="card-body p-4 text-center">
                    <span class="badge bg-success mb-2">Popular</span>
                    <div class="mb-2"><i class="mdi mdi-coin text-success" style="font-size: 2rem;"></i></div>
                    <h5 class="fw-600 mb-1">10 Credits</h5>
                    <p class="text-muted small mb-3">10 job posts</p>
                    <a href="{{ route('contact') }}?subject=Buy%2010%20credits" class="btn btn-success btn-sm w-100">Contact to purchase</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card employer-card h-100">
                <div class="card-body p-4 text-center">
                    <div class="mb-2"><i class="mdi mdi-coin text-dark" style="font-size: 2rem;"></i></div>
                    <h5 class="fw-600 mb-1">25 Credits</h5>
                    <p class="text-muted small mb-3">25 job posts</p>
                    <a href="{{ route('contact') }}?subject=Buy%2025%20credits" class="btn btn-outline-dark btn-sm w-100">Contact to purchase</a>
                </div>
            </div>
        </div>
    </div>
    <p class="text-muted small mt-3 mb-0">Need a custom package? <a href="{{ route('contact') }}">Contact us</a> and we’ll set it up for you.</p>
@endsection
