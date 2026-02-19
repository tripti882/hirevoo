@extends('layouts.app')

@section('title', 'Resume Results - ATS Score')

@push('styles')
<style>
    .resume-score-ring {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        position: relative;
        background: var(--score-bg);
        border: 6px solid var(--score-border);
    }
    .resume-score-ring.score-high { --score-bg: rgba(16, 185, 129, 0.12); --score-border: rgba(16, 185, 129, 0.4); color: #059669; }
    .resume-score-ring.score-mid { --score-bg: rgba(245, 158, 11, 0.12); --score-border: rgba(245, 158, 11, 0.4); color: #b45309; }
    .resume-score-ring.score-low { --score-bg: rgba(239, 68, 68, 0.12); --score-border: rgba(239, 68, 68, 0.4); color: #dc2626; }
    .job-goal-card {
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    .job-goal-card:hover {
        box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.08);
    }
    .match-bar {
        height: 6px;
        border-radius: 3px;
        background: var(--bs-light);
        overflow: hidden;
    }
    .match-bar-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.5s ease;
    }
    .resume-section-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }
</style>
@endpush

@section('content')
    <!-- Start page title -->
    <section class="page-title-box">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="text-center text-white">
                        <h3 class="mb-2">Your Resume Analysis</h3>
                        <p class="mb-0 opacity-75 small">ATS score, summary, and job recommendations</p>
                        <div class="page-next mt-3">
                            <nav class="d-inline-block" aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50">Home</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('resume.upload') }}" class="text-white-50">Submit CV</a></li>
                                    <li class="breadcrumb-item active text-white" aria-current="page">Results</li>
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

    <section class="section pb-5">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="uil uil-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-9 mx-auto">
                    <!-- Results illustration -->
                    <div class="text-center mb-4 d-none d-md-block">
                        <img src="{{ asset('images/ats-results.svg') }}" alt="Resume analysis" class="img-fluid" style="max-height: 200px;">
                    </div>
                    <!-- File info bar -->
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4 p-3 rounded-3 bg-light">
                        <i class="uil uil-file-alt text-primary fs-20"></i>
                        <span class="fw-medium">{{ $resume->file_name ?? 'Resume' }}</span>
                        @if($resume->created_at)
                            <span class="text-muted small">· Uploaded {{ $resume->created_at->diffForHumans() }}</span>
                        @endif
                    </div>

                    <!-- ATS Score block -->
                    @php
                        $score = $resume->ai_score ?? 0;
                        $scoreClass = $score >= 70 ? 'score-high' : ($score >= 50 ? 'score-mid' : 'score-low');
                        $band = $score >= 70 ? 'Good' : ($score >= 50 ? 'Fair' : 'Needs work');
                        $bandBg = $score >= 70 ? 'success' : ($score >= 50 ? 'warning' : 'danger');
                    @endphp
                    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row align-items-center">
                                <div class="col-auto text-center mb-4 mb-md-0">
                                    <div class="resume-score-ring {{ $scoreClass }} mx-auto">
                                        {{ $score }}<span class="fs-18 opacity-75">%</span>
                                    </div>
                                    <span class="badge bg-{{ $bandBg }} mt-2">{{ $band }}</span>
                                </div>
                                <div class="col">
                                    <h4 class="mb-2">ATS Compatibility Score</h4>
                                    <p class="text-muted small mb-3">How well your resume is likely to parse in applicant tracking systems used by recruiters.</p>
                                    <div class="match-bar mb-3">
                                        <div class="match-bar-fill bg-{{ $bandBg }}" style="width: {{ $score }}%;"></div>
                                    </div>
                                    @if($resume->ai_score_explanation)
                                        <div class="p-3 rounded-3 bg-light border-start border-3 border-{{ $bandBg }}">
                                            <p class="mb-0 text-dark small">{{ $resume->ai_score_explanation }}</p>
                                        </div>
                                    @endif
                                    <button type="button" class="btn btn-link btn-sm p-0 text-muted mt-2" data-bs-toggle="collapse" data-bs-target="#whatIsAts">
                                        What is ATS? <i class="uil uil-angle-down"></i>
                                    </button>
                                    <div class="collapse mt-2" id="whatIsAts">
                                        <p class="small text-muted mb-0">ATS (Applicant Tracking System) is software used by companies to filter resumes. It looks for clear sections, relevant keywords, and readable formatting. A higher score means your resume is more likely to reach a recruiter.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($resume->ai_summary)
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="resume-section-icon bg-primary bg-opacity-10 text-primary me-3">
                                    <i class="uil uil-document-layout-left"></i>
                                </div>
                                <h5 class="mb-0">Resume Summary</h5>
                            </div>
                            <p class="text-muted mb-0 lh-lg">{{ $resume->ai_summary }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Extracted skills -->
                    @php $skills = is_array($resume->extracted_skills) ? $resume->extracted_skills : []; @endphp
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="resume-section-icon bg-success bg-opacity-10 text-success me-3">
                                    <i class="uil uil-award"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Extracted Skills</h5>
                                    <p class="text-muted small mb-0">Used to match you with job goals below</p>
                                </div>
                            </div>
                            @if(count($skills) > 0)
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($skills as $skill)
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">{{ is_string($skill) ? $skill : '' }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">No skills detected. Add a clear Skills section with technologies and tools you use.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Recommended job goals -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="resume-section-icon bg-info bg-opacity-10 text-info me-3">
                                    <i class="uil uil-briefcase-alt"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Recommended Job Goals</h5>
                                    <p class="text-muted small mb-0">Match % = required skills found in your resume</p>
                                </div>
                            </div>
                            @if(!empty($recommendedJobGoals))
                                <div class="row g-3">
                                    @foreach($recommendedJobGoals as $item)
                                        @php $role = $item['job_role']; $match = $item['match_percentage']; @endphp
                                        <div class="col-12">
                                            <div class="job-goal-card card border rounded-3 h-100">
                                                <div class="card-body p-4">
                                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                                        <div class="d-flex align-items-center flex-grow-1 min-w-0">
                                                            <div class="flex-shrink-0 rounded-3 bg-primary bg-opacity-10 text-primary p-2 me-3">
                                                                <i class="uil uil-bag fs-20"></i>
                                                            </div>
                                                            <div class="min-w-0">
                                                                <a href="{{ route('job-goal.show', $role) }}" class="fw-semibold text-dark text-decoration-none stretched-link">{{ $role->title }}</a>
                                                                <div class="match-bar mt-2 mb-1" style="max-width: 120px;">
                                                                    <div class="match-bar-fill {{ $match >= 70 ? 'bg-success' : ($match >= 40 ? 'bg-warning' : 'bg-secondary') }}" style="width: {{ $match }}%;"></div>
                                                                </div>
                                                                <span class="small text-muted">{{ $match }}% match</span>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-2 flex-shrink-0">
                                                            <a href="{{ route('job-goal.show', $role) }}" class="btn btn-soft-primary btn-sm">View skills</a>
                                                            @if(in_array($role->id, $appliedJobIds ?? []))
                                                                <span class="badge bg-success align-self-center">Applied</span>
                                                            @else
                                                                <a href="{{ route('job-goal.apply', $role) }}" class="btn btn-primary btn-sm"><i class="uil uil-import me-1"></i> Apply</a>
                                                            @endif
                                                            <form action="{{ route('resume.lead') }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="resume_id" value="{{ $resume->id }}">
                                                                <input type="hidden" name="job_role_id" value="{{ $role->id }}">
                                                                <button type="submit" class="btn btn-soft-success btn-sm">Get help to learn</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    @if(!empty($item['missing_skills']) && count($item['missing_skills']) > 0)
                                                        <p class="small text-muted mb-0 mt-2 pt-2 border-top">Missing: {{ implode(', ', array_slice($item['missing_skills'], 0, 6)) }}{{ count($item['missing_skills']) > 6 ? '...' : '' }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">No job roles to match. <a href="{{ route('job-list') }}">Browse Job Goals</a></p>
                            @endif
                        </div>
                    </div>

                    <!-- Tips -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4 border-primary border-opacity-25">
                        <div class="card-body p-4">
                            <h6 class="mb-3"><i class="uil uil-lightbulb-alt text-primary me-2"></i>Tips to improve your ATS score</h6>
                            <div class="row g-2">
                                <div class="col-md-6"><div class="d-flex align-items-start small text-muted"><i class="uil uil-check-circle text-success me-2 mt-1"></i><span>Use clear section headings: Experience, Education, Skills</span></div></div>
                                <div class="col-md-6"><div class="d-flex align-items-start small text-muted"><i class="uil uil-check-circle text-success me-2 mt-1"></i><span>Include keywords from the job description</span></div></div>
                                <div class="col-md-6"><div class="d-flex align-items-start small text-muted"><i class="uil uil-check-circle text-success me-2 mt-1"></i><span>Add quantifiable achievements (e.g. "Increased X by 20%")</span></div></div>
                                <div class="col-md-6"><div class="d-flex align-items-start small text-muted"><i class="uil uil-check-circle text-success me-2 mt-1"></i><span>Keep formatting simple; avoid complex tables</span></div></div>
                                <div class="col-md-6"><div class="d-flex align-items-start small text-muted"><i class="uil uil-check-circle text-success me-2 mt-1"></i><span>List technical skills explicitly in a Skills section</span></div></div>
                            </div>
                        </div>
                    </div>

                    <!-- CTA + Actions -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1">Request Referral</h6>
                                    <p class="text-muted small mb-0">Get referrals from verified employees. Upgrade to Premium.</p>
                                </div>
                                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                                    <a href="{{ route('pricing') }}" class="btn btn-primary">View Premium (₹999)</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('resume.upload') }}" class="btn btn-primary me-2"><i class="uil uil-file-upload me-1"></i> Upload another CV</a>
                        <a href="{{ route('home') }}" class="btn btn-outline-primary"><i class="uil uil-arrow-left me-1"></i> Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
