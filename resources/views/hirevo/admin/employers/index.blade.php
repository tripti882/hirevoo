@extends('layouts.app')

@section('title', 'Manage Employers')

@section('content')
    <section class="page-title-box">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="text-center text-white">
                        <h3 class="mb-4">Manage Employers</h3>
                        <div class="page-next">
                            <nav class="d-inline-block" aria-label="breadcrumb text-center">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('admin.employers.index') }}">Admin</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Employers</li>
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
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card border shadow-none rounded-3">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                        <h5 class="mb-0">Employer list</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.employers.index') }}" class="btn btn-sm {{ request('status') !== 'pending' && !request('status') ? 'btn-primary' : 'btn-soft-primary' }}">All</a>
                            <a href="{{ route('admin.employers.index', ['status' => 'pending']) }}" class="btn btn-sm {{ request('status') === 'pending' ? 'btn-primary' : 'btn-soft-primary' }}">Pending</a>
                            <a href="{{ route('admin.employers.index', ['status' => 'approved']) }}" class="btn btn-sm {{ request('status') === 'approved' ? 'btn-primary' : 'btn-soft-primary' }}">Approved</a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-centered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Company</th>
                                    <th>Company email</th>
                                    <th>Designation</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employers as $user)
                                    @php $profile = $user->referrerProfile; @endphp
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $profile?->company_name ?? '—' }}</td>
                                        <td>{{ $profile?->company_email ?? '—' }}</td>
                                        <td>{{ $profile?->designation ?? '—' }}</td>
                                        <td>
                                            @if($profile && $profile->is_approved)
                                                <span class="badge bg-success">Approved</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($profile)
                                                @if($profile->is_approved)
                                                    <form method="POST" action="{{ route('admin.employers.reject', ['employer' => $user]) }}" class="d-inline" onsubmit="return confirm('Reject this employer?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('admin.employers.approve', ['employer' => $user]) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success me-1">Approve</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.employers.reject', ['employer' => $user]) }}" class="d-inline" onsubmit="return confirm('Reject this employer?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                                                    </form>
                                                @endif
                                            @else
                                                <span class="text-muted small">No profile</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No employers found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($employers->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $employers->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
