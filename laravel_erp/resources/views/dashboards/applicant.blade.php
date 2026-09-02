<div class="eyebrow">Applicant workspace</div>
<h1 class="heading">Your journey to MEMA starts here.</h1>
<p class="sub">Complete your application, confirm payment and follow every decision in one place.</p>

<section class="panel" style="margin-top:20px">
    <div class="panel-head">
        <div>
            <h2>{{ $application?->offering?->course?->name ?? 'College application' }}</h2>
            <small style="color:var(--muted)">{{ $application?->application_number ?? 'No application started' }}</small>
        </div>
        @if($application)
            <span class="badge">{{ str($application->status)->replace('_', ' ')->title() }}</span>
        @endif
    </div>
    <div class="panel-body">
        <p style="color:var(--muted);margin-top:0">Your applicant portal keeps drafts, supporting documents, payment and the final offer together.</p>
        <a class="btn btn-primary" href="{{ $application ? route('admissions.portal') : route('admissions.catalogue') }}">
            <i data-lucide="arrow-right-circle"></i>{{ $application ? 'Continue application' : 'Explore programmes' }}
        </a>
    </div>
</section>
