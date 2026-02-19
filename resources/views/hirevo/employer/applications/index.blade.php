@extends('layouts.employer')

@section('title', 'Applications – ' . $job->title)

@section('header_title', 'Applications')

@section('header_actions')
    <a href="{{ route('employer.jobs.index') }}" class="btn btn-outline-primary btn-sm">Back to jobs</a>
@endsection

@section('content')
    <div class="mb-4">
        <h5 class="mb-1">{{ $job->title }}</h5>
        <p class="text-muted small mb-0">{{ $job->location ?? '—' }} · {{ ucfirst($job->status) }}</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($applications->isEmpty())
        <div class="card employer-card">
            <div class="card-body p-4">
                <p class="text-muted mb-0">No applications yet for this job.</p>
            </div>
        </div>
    @else
        <p class="text-muted small mb-3">{{ $applications->count() }} applicant(s)</p>
        @foreach($applications as $app)
            @include('hirevo.employer.applications._applicant-card', ['app' => $app])
        @endforeach
    @endif

    @push('scripts')
    <script>
        document.querySelectorAll('.application-status-select').forEach(function(el) {
            el.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });
    </script>
    @endpush
@endsection
