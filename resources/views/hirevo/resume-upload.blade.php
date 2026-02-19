@extends('layouts.app')

@section('title', 'Submit CV - Get ATS Score')

@push('styles')
<style>
    .upload-zone {
        border: 2px dashed rgba(11, 31, 59, 0.2);
        border-radius: 16px;
        padding: 2.5rem;
        text-align: center;
        transition: border-color 0.2s, background 0.2s;
        background: rgba(11, 31, 59, 0.02);
        min-height: 200px;
        position: relative;
    }
    .upload-zone:hover, .upload-zone.dragover {
        border-color: var(--bs-primary);
        background: rgba(11, 31, 59, 0.04);
    }
    .upload-zone .upload-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin: 0 auto 1rem;
        background: var(--bs-primary);
        color: #fff;
    }
    .step-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    .step-num {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--bs-primary);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.9rem;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')
    <!-- Start page title -->
    <section class="page-title-box">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center text-white">
                        <h3 class="mb-3">Get your resume scored and find matching job goals</h3>
                        <p class="mb-0 opacity-75">Upload your CV. We'll show your ATS score, a short summary, and recommend job goals that match your skills.</p>
                        <div class="page-next mt-3">
                            <nav class="d-inline-block" aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50">Home</a></li>
                                    <li class="breadcrumb-item active text-white" aria-current="page">Submit CV</li>
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
            <div class="row justify-content-center align-items-center">
                <div class="col-lg-5 col-md-6 d-none d-md-block text-center mb-4 mb-lg-0">
                    <img src="{{ asset('images/resume-upload.svg') }}" alt="Upload resume" class="img-fluid" style="max-height: 280px;">
                </div>
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-body p-4 p-lg-5">
                            <form action="{{ route('resume.upload') }}" method="POST" enctype="multipart/form-data" id="resumeForm">
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
                                <div class="mb-4 position-relative">
                                    <div class="upload-zone position-relative" id="uploadZone" style="cursor: pointer;">
                                        <input type="file" name="resume" id="resume" class="position-absolute @error('resume') is-invalid @enderror" accept=".pdf,application/pdf" required style="cursor: pointer; width: 100%; height: 100%; top: 0; left: 0; opacity: 0;">
                                        <div class="upload-icon">
                                            <i class="uil uil-file-upload"></i>
                                        </div>
                                        <h5 class="mb-2">Drop your resume here or click to browse</h5>
                                        <p class="text-muted small mb-0">PDF only, max 10 MB</p>
                                    </div>
                                    <p class="small text-muted mt-2 mb-0" id="fileName"></p>
                                    @error('resume')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-hover px-4 py-2" id="submitBtn"><i class="uil uil-analyze me-1"></i> Analyze my resume</button>
                                </div>
                            </form>

                            <hr class="my-4">

                            <h6 class="mb-3">What you'll get</h6>
                            <div class="step-item mb-2">
                                <span class="step-num">1</span>
                                <div><strong>ATS score</strong> — How well your resume parses in recruiter systems</div>
                            </div>
                            <div class="step-item mb-2">
                                <span class="step-num">2</span>
                                <div><strong>Summary & skills</strong> — AI-generated summary and extracted skills</div>
                            </div>
                            <div class="step-item">
                                <span class="step-num">3</span>
                                <div><strong>Job recommendations</strong> — Roles that match your skills, with match %</div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="{{ route('home') }}" class="btn btn-outline-primary"><i class="uil uil-arrow-left me-1"></i> Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
    (function() {
        var zone = document.getElementById('uploadZone');
        var input = document.getElementById('resume');
        var fileName = document.getElementById('fileName');
        var submitBtn = document.getElementById('submitBtn');
        if (!zone || !input) return;
        function showFile(name) {
            fileName.textContent = name ? 'Selected: ' + name : '';
            if (submitBtn) submitBtn.disabled = !name;
        }
        zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('dragover'); });
        zone.addEventListener('dragleave', function() { zone.classList.remove('dragover'); });
        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            zone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                showFile(e.dataTransfer.files[0].name);
            }
        });
        input.addEventListener('change', function() {
            if (input.files.length) showFile(input.files[0].name); else showFile('');
        });
        if (input.files.length) showFile(input.files[0].name); else if (submitBtn) submitBtn.disabled = true;
    })();
    </script>
    @endpush
@endsection
