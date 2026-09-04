@extends('layouts.app')

@section('title', 'Admissions & Applicant Portal')
@section('section', 'Applicant Workspace')

@section('content')
<style>
    .portal-btn-primary {
        background-color: #0A3E50 !important;
        color: #ffffff !important;
        text-decoration: none !important;
    }
    .portal-btn-primary:hover {
        background-color: #072c39 !important;
        color: #ffffff !important;
    }
    .portal-btn-secondary {
        background-color: #1E8449 !important;
        color: #ffffff !important;
        text-decoration: none !important;
    }
    .portal-btn-secondary:hover {
        background-color: #166534 !important;
        color: #ffffff !important;
    }
    .portal-btn-accent {
        background-color: #E67E22 !important;
        color: #ffffff !important;
        text-decoration: none !important;
    }
    .portal-btn-accent:hover {
        background-color: #d35400 !important;
        color: #ffffff !important;
    }
</style>

<div class="mema-dashboard-container py-2 font-quicksand" style="max-width:1240px;margin:0 auto;">

    <!-- Top Navigation & Applicant Switcher Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-[#0A3E50]/10 text-[#0A3E50]">
                    Applicant Onboarding Workspace
                </span>
                @if($isAdminOrStaff)
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        Admin / Staff Review Mode
                    </span>
                @endif
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-[#0A3E50] mt-1.5 tracking-tight">
                {{ $application ? ($application->applicant?->user?->name . ' • ' . $application->offering?->course?->name) : 'MEMA Admissions Command & Application Portal' }}
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Manage admission applications, verify supporting evidence, settle application fees, and accept offers.
            </p>
        </div>

        <!-- Quick Switcher Dropdown & Actions -->
        <div class="flex items-center gap-2.5 flex-wrap">
            @if($allApplications->isNotEmpty())
                <div class="relative">
                    <select onchange="if(this.value) window.location.href='{{ route('admissions.portal') }}?application_id=' + this.value" class="bg-white border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 shadow-2xs focus:outline-none focus:border-[#0A3E50] cursor-pointer">
                        <option value="">-- Switch Application Dossier ({{ $allApplications->count() }}) --</option>
                        @foreach($allApplications as $appItem)
                            <option value="{{ $appItem->id }}" @if($application && $application->id === $appItem->id) selected @endif>
                                {{ $appItem->applicant?->user?->name ?? 'Applicant' }} ({{ $appItem->offering?->course?->code ?? 'Programme' }}) - {{ $appItem->application_number }} [{{ $appItem->status }}]
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <a href="{{ route('admissions.catalogue') }}" class="px-4 py-2 rounded-xl portal-btn-primary font-extrabold text-xs shadow-md inline-flex items-center gap-1.5">
                <i data-lucide="compass" class="w-4 h-4 text-[#E67E22]"></i>
                <span>Explore Programmes</span>
            </a>
        </div>
    </div>

@if($application)
    @php
        $paid = $application->isPaid();
        $canEdit = in_array($application->status, ['DRAFT', 'RETURNED_FOR_CORRECTION', 'INFO_REQUESTED']);
        $providers = (array) config('admission.payments.providers', []);
        $feeAmount = (float) ($application->fee_amount_expected ?? config('admission.fee.amount', 1500));
        $feeCurrency = $application->fee_currency ?? config('admission.fee.currency', 'KES');
        $settledAttempt = $application->payments->firstWhere('status', 'PAID')
            ?? $application->payments->firstWhere('status', 'WAIVED');
        $openAttempt = $application->payments
            ->whereIn('status', ['INITIATED', 'PENDING', 'AWAITING_VERIFICATION'])
            ->sortByDesc('created_at')
            ->first();
        $methods = collect([
            ['key' => 'mpesa', 'provider' => 'mpesa_stk', 'icon' => 'smartphone', 'label' => 'M-Pesa STK Push', 'blurb' => 'A prompt is sent to your phone. Enter your M-Pesa PIN to pay.', 'needs_phone' => true],
            ['key' => 'paybill', 'provider' => 'mpesa_c2b', 'icon' => 'hash', 'label' => 'M-Pesa Paybill', 'blurb' => 'Pay to the paybill below using your application number as the account.', 'needs_phone' => false],
            ['key' => 'card', 'provider' => 'card', 'icon' => 'credit-card', 'label' => 'Debit or Credit Card', 'blurb' => 'You will be redirected to our secure card processor.', 'needs_phone' => false],
            ['key' => 'bank', 'provider' => 'bank_transfer', 'icon' => 'landmark', 'label' => 'Bank Transfer or Deposit', 'blurb' => 'Deposit at any branch, then enter the transaction reference here.', 'needs_phone' => false],
            ['key' => 'cashier', 'provider' => 'cashier', 'icon' => 'building-2', 'label' => 'College Cashier', 'blurb' => 'Pay at the finance office and enter the receipt number here.', 'needs_phone' => false],
        ])->filter(fn (array $method): bool => (bool) data_get($providers, $method['provider'].'.enabled', false))->values();
    @endphp

    {{-- Top Application Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6 bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-mono text-xs font-extrabold text-[#0A3E50] bg-teal-50 px-2.5 py-0.5 rounded-md border border-teal-200 shadow-2xs">
                    REF: {{ $application->application_number }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold
                    @if(in_array($application->status, ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])) bg-emerald-50 text-emerald-800 border border-emerald-200
                    @elseif(in_array($application->status, ['UNDER_REVIEW', 'SHORTLISTED', 'APPROVAL_PENDING', 'SUBMITTED'])) bg-blue-50 text-blue-800 border border-blue-200
                    @elseif(in_array($application->status, ['RETURNED_FOR_CORRECTION', 'INFO_REQUESTED', 'WAITLISTED', 'DRAFT'])) bg-amber-50 text-amber-800 border border-amber-200
                    @else bg-rose-50 text-rose-800 border border-rose-200 @endif">
                    <span class="w-1.5 h-1.5 rounded-full 
                        @if(in_array($application->status, ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])) bg-emerald-500
                        @elseif(in_array($application->status, ['UNDER_REVIEW', 'SHORTLISTED', 'APPROVAL_PENDING', 'SUBMITTED'])) bg-blue-500
                        @elseif(in_array($application->status, ['RETURNED_FOR_CORRECTION', 'INFO_REQUESTED', 'WAITLISTED', 'DRAFT'])) bg-amber-500
                        @else bg-rose-500 @endif"></span>
                    {{ str_replace('_', ' ', $application->status) }}
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-2 tracking-tight">{{ $application->offering?->course?->name ?? 'Course Programme' }}</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                {{ $application->offering?->intake?->name ?? 'September Intake' }} · {{ $application->offering?->campus ?? 'Main Campus' }} ({{ $application->study_mode ?? 'Full-Time' }})
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @if(in_array($application->status, ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED']))
                <a href="{{ route('admissions.application.letter', $application) }}" target="_blank" class="px-4 py-2 rounded-xl portal-btn-secondary font-extrabold text-xs shadow-md inline-flex items-center gap-1.5">
                    <i data-lucide="printer" class="w-4 h-4 text-white"></i>
                    <span>Official Offer Letter (PDF)</span>
                </a>
            @endif
            <a href="{{ route('admissions.catalogue') }}" target="_blank" class="px-3.5 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition-colors shadow-2xs flex items-center gap-1.5">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Prospectus
            </a>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Candidate / ID</div>
            <div class="text-lg font-extrabold text-[#0A3E50] mt-2 mb-1 leading-none font-mono">{{ $application->applicant?->applicant_number ?? 'APP-2026' }}</div>
            <p class="text-[11px] text-slate-500 mb-2">{{ $application->applicant?->user?->name ?? 'Applicant Profile' }}</p>
            <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Verified Identity</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Completion Progress</div>
            <div class="text-2xl font-extrabold text-purple-900 mt-2 mb-1 leading-none">{{ $application->completion_percent }}%</div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 mb-2 overflow-hidden">
                <div class="bg-[#0A3E50] h-1.5 rounded-full" style="width: {{ $application->completion_percent }}%"></div>
            </div>
            <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold text-purple-800 bg-purple-50 border border-purple-200">Required Steps</span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Processing Fee</div>
            <div class="text-xl font-extrabold @if($paid) text-emerald-700 @else text-amber-700 @endif mt-2 mb-1 leading-none">
                {{ $paid ? 'KES '.number_format((float) config('admission.fee.amount'), 0).' Paid' : 'KES '.number_format((float) config('admission.fee.amount'), 0).' Due' }}
            </div>
            <p class="text-[11px] text-slate-500 mb-2">{{ $paid ? 'Settled via M-Pesa' : 'Payment required to submit' }}</p>
            <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold @if($paid) text-emerald-800 bg-emerald-50 border border-emerald-200 @else text-amber-800 bg-amber-50 border border-amber-200 @endif">
                {{ $paid ? 'Confirmed & Verified' : 'Pending Payment' }}
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Current Stage</div>
            <div class="text-base font-extrabold text-[#0A3E50] mt-2 mb-1 leading-none">
                @if($application->status === 'DRAFT') Draft in Progress
                @elseif($application->status === 'SUBMITTED') Under Review
                @elseif($application->status === 'ADMITTED') Offer Dispatched
                @elseif($application->status === 'ENROLLED') Registered Student
                @else {{ str_replace('_', ' ', $application->status) }} @endif
            </div>
            <p class="text-[11px] text-slate-500 mb-2">Updated {{ $application->updated_at->diffForHumans() }}</p>
            <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold text-slate-700 bg-slate-100 border border-slate-200">Live Status</span>
        </div>
    </div>

    {{-- Correction Notice Banner if returned --}}
    @if(in_array($application->status, ['RETURNED_FOR_CORRECTION', 'INFO_REQUESTED']))
        <div class="mb-6 p-5 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 flex items-start gap-3 shadow-2xs">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
            <div>
                <strong class="block font-bold text-sm text-amber-900">Application Returned for Information / Correction</strong>
                <p class="text-xs text-amber-800 mt-1">
                    {{ $application->histories()->latest()->value('note') ?? 'Please review your uploaded documents and academic qualifications, make the required corrections below, and resubmit your application.' }}
                </p>
            </div>
        </div>
    @endif

    {{-- Main Workspace Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        {{-- Left Form / Content Area (2 cols) --}}
        <div class="lg:col-span-2 space-y-6">
            @if($canEdit)
                {{-- Resumable Application Form --}}
                <form method="post" action="{{ route('admissions.application.update', $application) }}" data-autosave-form data-application-id="{{ $application->id }}" class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="lock_version" value="{{ $application->lock_version }}">

                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <div>
                            <div class="text-[11px] font-bold text-[#E67E22] uppercase tracking-wider">Step 1 of 4</div>
                            <h2 class="text-sm font-extrabold text-[#0A3E50]">Personal &amp; Contact Details</h2>
                        </div>
                        <span data-save-status aria-live="polite" class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                            Auto-Save Active
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Date of Birth *</label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $application->applicant?->date_of_birth ? (is_string($application->applicant->date_of_birth) ? $application->applicant->date_of_birth : $application->applicant->date_of_birth->format('Y-m-d')) : '') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]" required>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Gender *</label>
                            <select name="gender" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 bg-white focus:outline-none focus:border-[#0A3E50]" required>
                                <option value="">Select Gender</option>
                                <option value="M" @if(old('gender', $application->form_data['gender'] ?? '') === 'M') selected @endif>Male</option>
                                <option value="F" @if(old('gender', $application->form_data['gender'] ?? '') === 'F') selected @endif>Female</option>
                                <option value="N" @if(old('gender', $application->form_data['gender'] ?? '') === 'N') selected @endif>Prefer not to say</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nationality *</label>
                            <input name="nationality" value="{{ old('nationality', $application->applicant?->nationality ?? 'Kenyan') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]" required>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">County / Region *</label>
                            <input name="county" value="{{ old('county', $application->applicant?->county) }}" placeholder="e.g. Nairobi" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]" required>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Identity Document Type *</label>
                            <select name="identity_type" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 bg-white focus:outline-none focus:border-[#0A3E50]" required>
                                <option value="national_id" @if(old('identity_type', $application->applicant?->identity_type) === 'national_id') selected @endif>National ID Card</option>
                                <option value="birth_certificate" @if(old('identity_type', $application->applicant?->identity_type) === 'birth_certificate') selected @endif>Birth Certificate</option>
                                <option value="passport" @if(old('identity_type', $application->applicant?->identity_type) === 'passport') selected @endif>Passport</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Identity / Document Number *</label>
                            <input name="identity_number" value="{{ old('identity_number', $application->applicant?->identity_number) }}" placeholder="e.g. 38472910" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]" required>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <label class="block font-bold text-slate-700 mb-1">Step 2: Academic Background &amp; Entry Qualifications *</label>
                        <textarea name="education" rows="3" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]" placeholder="Enter KCSE index number, mean grade (e.g. B+), cluster subject scores, or prior post-secondary diplomas..." required>{{ old('education', $application->form_data['education'] ?? '') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-2">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">How did you hear about us?</label>
                            <select name="source_channel" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 bg-white focus:outline-none focus:border-[#0A3E50]">
                                <option value="Website">University Website</option>
                                <option value="Social media">Social Media</option>
                                <option value="School visit">School Outreach / Career Day</option>
                                <option value="Friend or family">Friend / Family Referral</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="flex items-center pt-5">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                <input type="checkbox" name="has_support_need" value="1" @if($application->applicant?->has_support_need) checked @endif class="rounded text-[#0A3E50] focus:ring-[#0A3E50]">
                                <span>I require learning accessibility support</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-2">
                        <label class="flex items-start gap-2 cursor-pointer text-xs text-slate-700">
                            <input type="checkbox" name="declarations_accepted" value="1" checked class="mt-0.5 rounded text-[#0A3E50] focus:ring-[#0A3E50]" required>
                            <span>I declare that all information provided and supporting documents uploaded are authentic and accurate.</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl portal-btn-primary font-extrabold text-xs transition-colors shadow-sm">
                        Save Application Details
                    </button>
                </form>

                {{-- Document Upload Box --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <div>
                            <div class="text-[11px] font-bold text-[#E67E22] uppercase tracking-wider">Step 3 of 4</div>
                            <h2 class="text-sm font-extrabold text-[#0A3E50]">Supporting Documents Upload</h2>
                        </div>
                        <span class="text-[11px] font-bold text-purple-800 bg-purple-50 px-2.5 py-0.5 rounded-full border border-purple-200">
                            {{ $application->documents->count() }} Uploaded
                        </span>
                    </div>

                    <form method="post" enctype="multipart/form-data" action="{{ route('admissions.application.documents', $application) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Document Category *</label>
                                <select name="document_type" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 bg-white focus:outline-none focus:border-[#0A3E50]">
                                    <option value="identity">National ID / Birth Certificate</option>
                                    <option value="certificate">KCSE / Academic Certificate</option>
                                    <option value="transcript">Official Academic Transcript</option>
                                    <option value="photo">Passport Photograph</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Select File (PDF, JPG, PNG &le; 5MB) *</label>
                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#0A3E50]/10 file:text-[#0A3E50] hover:file:bg-[#0A3E50]/20" required>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl portal-btn-primary font-extrabold text-xs transition-colors shadow-sm flex items-center justify-center gap-1.5">
                            <i data-lucide="upload" class="w-3.5 h-3.5 text-[#E67E22]"></i> Upload Document
                        </button>
                    </form>

                    {{-- List of Uploaded Documents --}}
                    @if($application->documents->isNotEmpty())
                        <div class="pt-3 border-t border-slate-100 space-y-2">
                            @foreach($application->documents as $doc)
                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="file-check" class="w-4 h-4 text-[#0A3E50]"></i>
                                        <div>
                                            <span class="font-bold text-slate-800">{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}:</span>
                                            <span class="text-slate-600">{{ $doc->original_name }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold @if($doc->verification_status==='VERIFIED') bg-emerald-100 text-emerald-800 @else bg-amber-100 text-amber-800 @endif">
                                            {{ $doc->verification_status }}
                                        </span>
                                        <a href="{{ route('admissions.document.download', $doc) }}" target="_blank" class="text-[#0A3E50] hover:underline font-bold text-[11px]">Download</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Payment Gateway Hub --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-5" id="payment-gateway-container">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <div>
                            <div class="text-[11px] font-bold text-[#E67E22] uppercase tracking-wider">Step 4 of 4 • Executive Settlement</div>
                            <h2 class="text-sm font-extrabold text-[#0A3E50]">Official Application Fee Checkout (KES {{ number_format((float) config('admission.fee.amount'), 0) }})</h2>
                        </div>
                        <span class="inline-flex items-center gap-1 px-3 py-0.5 rounded-full text-xs font-bold @if($paid) bg-emerald-50 text-emerald-800 border border-emerald-200 @else bg-amber-50 text-amber-800 border border-amber-200 @endif">
                            <span class="w-1.5 h-1.5 rounded-full @if($paid) bg-emerald-500 @else bg-amber-500 animate-ping @endif"></span>
                            {{ $paid ? 'CONFIRMED & SETTLED' : 'PAYMENT DUE' }}
                        </span>
                    </div>

                    @if(!$paid)
                        @if($openAttempt)
                            {{-- An attempt is open. Until a provider callback or a Finance
                                 Officer confirms it, nothing has been settled. --}}
                            <div class="p-5 rounded-xl border border-amber-300 bg-amber-50/60 space-y-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <strong class="font-extrabold text-sm text-amber-950 block">
                                            {{ $openAttempt->status === 'AWAITING_VERIFICATION' ? 'Awaiting Finance confirmation' : 'Payment started — not yet confirmed' }}
                                        </strong>
                                        <p class="text-[11.5px] text-slate-700 mt-0.5">
                                            @if($openAttempt->status === 'AWAITING_VERIFICATION')
                                                Your transaction code is with the Finance office. Your receipt appears here once it is verified.
                                            @else
                                                Complete the payment on your phone or at the bank, then enter the transaction code below.
                                            @endif
                                        </p>
                                        <div class="font-mono text-[11px] text-slate-600 mt-1">
                                            Reference: {{ $openAttempt->reference }} • {{ strtoupper((string) $openAttempt->channel) }}
                                            @if($openAttempt->provider_request_ref) • Code: {{ $openAttempt->provider_request_ref }} @endif
                                        </div>
                                        @if($openAttempt->failure_reason)
                                            <p class="text-[11px] text-rose-700 font-bold mt-1">{{ $openAttempt->failure_reason }}</p>
                                        @endif
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-[10.5px] font-extrabold text-amber-900 bg-amber-100 border border-amber-300 whitespace-nowrap">
                                        {{ str_replace('_', ' ', $openAttempt->status) }}
                                    </span>
                                </div>

                                @if($openAttempt->channel === 'paybill' && data_get($providers, 'mpesa_c2b.shortcode'))
                                    <div class="bg-white rounded-lg border border-slate-200 p-3 text-[11.5px] text-slate-700 space-y-0.5">
                                        <div>Paybill: <strong class="font-mono">{{ data_get($providers, 'mpesa_c2b.shortcode') }}</strong></div>
                                        <div>Account number: <strong class="font-mono">{{ $application->application_number }}</strong></div>
                                    </div>
                                @elseif($openAttempt->channel === 'bank' && data_get($providers, 'bank_transfer.account_number'))
                                    <div class="bg-white rounded-lg border border-slate-200 p-3 text-[11.5px] text-slate-700 space-y-0.5">
                                        <div>{{ data_get($providers, 'bank_transfer.bank_name') }} — {{ data_get($providers, 'bank_transfer.branch') }}</div>
                                        <div>{{ data_get($providers, 'bank_transfer.account_name') }}</div>
                                        <div>Account: <strong class="font-mono">{{ data_get($providers, 'bank_transfer.account_number') }}</strong></div>
                                        <div>Reference: <strong class="font-mono">{{ $application->application_number }}</strong></div>
                                    </div>
                                @endif

                                @if($openAttempt->status !== 'AWAITING_VERIFICATION')
                                    <form method="post" action="{{ route('admissions.application.payment.declare', [$application, $openAttempt]) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1">Transaction code *</label>
                                            <input type="text" name="transaction_code" required value="{{ old('transaction_code') }}" placeholder="e.g. SFH4K2L9XZ"
                                                   class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono font-bold uppercase text-slate-900 focus:outline-none focus:border-[#1E8449]">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1">Name on the payment</label>
                                            <input type="text" name="payer_name" value="{{ old('payer_name', $application->applicant?->user?->name) }}"
                                                   class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-bold text-slate-900 focus:outline-none focus:border-[#1E8449]">
                                        </div>
                                        <button type="submit" class="w-full py-2 px-4 rounded-lg portal-btn-secondary font-extrabold text-xs shadow-md flex items-center justify-center gap-1.5">
                                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Submit for verification
                                        </button>
                                        @error('transaction_code')
                                            <p class="sm:col-span-3 text-[11px] font-bold text-rose-700">{{ $message }}</p>
                                        @enderror
                                    </form>
                                    <p class="text-[10.5px] text-slate-500">
                                        Entering a code does not settle the fee. Finance verifies it against the provider statement first.
                                    </p>
                                @endif
                            </div>
                        @endif

                        @if($methods->isEmpty())
                            <div class="p-5 rounded-xl border border-slate-300 bg-slate-50 text-xs text-slate-700">
                                No payment method is currently switched on. Contact the Admissions office to pay your application fee.
                            </div>
                        @elseif(!$openAttempt)
                            <div class="space-y-3">
                                @foreach($methods as $method)
                                    <div class="bg-emerald-50/40 p-5 rounded-xl border border-emerald-200 space-y-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <h3 class="font-extrabold text-xs text-slate-900 flex items-center gap-1.5">
                                                    <i data-lucide="{{ $method['icon'] }}" class="w-3.5 h-3.5 text-[#1E8449]"></i> {{ $method['label'] }}
                                                </h3>
                                                <p class="text-[11.5px] text-slate-600 mt-0.5">{{ $method['blurb'] }}</p>
                                            </div>
                                            <span class="font-mono font-bold text-xs text-emerald-800 bg-white px-2 py-0.5 rounded border border-emerald-200 whitespace-nowrap">
                                                {{ $feeCurrency }} {{ number_format($feeAmount, 0) }}
                                            </span>
                                        </div>

                                        <form method="post" action="{{ route('admissions.application.payment', $application) }}">
                                            @csrf
                                            <input type="hidden" name="channel" value="{{ $method['key'] }}">
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                                                @if($method['needs_phone'])
                                                    <div class="sm:col-span-2">
                                                        <label class="block text-xs font-bold text-slate-700 mb-1">M-Pesa mobile number *</label>
                                                        <div class="relative">
                                                            <input type="tel" name="phone" required placeholder="07XXXXXXXX"
                                                                   value="{{ old('phone', $application->applicant?->phone ?? $application->applicant?->user?->phone_number) }}"
                                                                   class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono font-bold text-slate-900 focus:outline-none focus:border-[#1E8449] pl-8 shadow-2xs">
                                                            <i data-lucide="phone" class="w-4 h-4 text-emerald-600 absolute left-2.5 top-2.5"></i>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="sm:col-span-2 text-[11px] text-slate-500 self-center">
                                                        Reference to quote: <strong class="font-mono text-slate-800">{{ $application->application_number }}</strong>
                                                    </div>
                                                @endif
                                                <button type="submit" class="w-full py-2 px-4 rounded-lg portal-btn-secondary font-extrabold text-xs shadow-md flex items-center justify-center gap-1.5">
                                                    <i data-lucide="send" class="w-3.5 h-3.5"></i> Pay {{ $feeCurrency }} {{ number_format($feeAmount, 0) }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endforeach
                                @error('channel') <p class="text-[11px] font-bold text-rose-700">{{ $message }}</p> @enderror
                                @error('phone') <p class="text-[11px] font-bold text-rose-700">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    @else
                        <div class="p-5 rounded-xl bg-emerald-50 border border-emerald-300 text-xs text-emerald-950 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 shadow-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold shadow-2xs">
                                    <i data-lucide="check-check" class="w-5 h-5 text-emerald-700"></i>
                                </div>
                                <div>
                                    <strong class="font-extrabold text-sm block">Payment cleared &amp; settled</strong>
                                    <div class="font-mono text-[11px] text-emerald-800 mt-0.5">Official receipt: {{ $settledAttempt?->receipt_number ?? '—' }}</div>
                                    <span class="text-[10.5px] text-slate-600">
                                        Channel: {{ strtoupper((string) ($settledAttempt?->channel ?? '—')) }}
                                        @if($settledAttempt?->paid_at) • Confirmed {{ $settledAttempt->paid_at->format('d M Y, H:i') }} @endif
                                    </span>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold text-emerald-900 bg-emerald-100 border border-emerald-300">CLEARED</span>
                        </div>
                    @endif
                </div>

                {{-- Final Submit Button --}}
                <form method="post" action="{{ route('admissions.application.submit', $application) }}">
                    @csrf
                    <button type="submit" class="w-full py-3.5 rounded-xl portal-btn-primary font-extrabold text-sm shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2" @disabled(!$paid || $application->completion_percent < 100)>
                        <span>Submit Application for Formal Review</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 text-[#E67E22]"></i>
                    </button>
                </form>
            @else
                {{-- Submitted Dossier Summary View --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <h2 class="text-sm font-extrabold text-[#0A3E50] uppercase tracking-wide">Submitted Application Dossier</h2>
                        <span class="font-mono text-xs text-slate-600 font-bold">{{ $application->application_number }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-slate-500 block">Candidate Name</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $application->applicant?->user?->name ?? 'Candidate' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Degree Programme</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $application->offering?->course?->name ?? 'Course Programme' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Intake &amp; Campus</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $application->offering?->intake?->name ?? 'September 2026' }} · {{ $application->offering?->campus ?? 'Main Campus' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Official Submission Receipt</span>
                            <span class="font-mono font-bold text-emerald-800">{{ $application->submission_receipt_number ?? 'MEMA/SUB/2026/001' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Offer Response Action if ADMITTED --}}
                @if($application->status === 'ADMITTED')
                    <div class="bg-white border-2 border-emerald-400 rounded-2xl p-6 shadow-md space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold shadow-2xs">
                                <i data-lucide="award" class="w-7 h-7 text-emerald-700"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-extrabold text-emerald-950">Congratulations! You Have Been Admitted</h2>
                                <p class="text-xs text-slate-600 mt-0.5">Please review your offer and confirm acceptance before {{ $application->offering?->intake?->acceptance_deadline ? date('d M, Y', strtotime($application->offering->intake->acceptance_deadline)) : '30 September 2026' }}.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-2 flex-wrap">
                            <form method="post" action="{{ route('admissions.application.respond', $application) }}" class="inline">
                                @csrf
                                <input type="hidden" name="response" value="ACCEPTED">
                                <input type="hidden" name="offer_declaration" value="1">
                                <button type="submit" class="px-6 py-2.5 rounded-xl portal-btn-secondary font-extrabold text-xs shadow-sm flex items-center gap-1.5">
                                    <i data-lucide="check" class="w-4 h-4"></i> Accept Admission Offer
                                </button>
                            </form>

                            <a href="{{ route('admissions.application.letter', $application) }}" target="_blank" class="px-4 py-2.5 rounded-xl border border-[#0A3E50] text-[#0A3E50] hover:bg-[#0A3E50]/5 font-extrabold text-xs transition-colors flex items-center gap-1.5">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download Offer Letter (PDF)
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Enrolment Action if READY_TO_ENROL --}}
                @if($application->status === 'READY_TO_ENROL')
                    <div class="bg-white border-2 border-[#0A3E50] rounded-2xl p-6 shadow-md space-y-4">
                        <div>
                            <h2 class="text-lg font-extrabold text-[#0A3E50]">Complete Your Enrolment Oath</h2>
                            <p class="text-xs text-slate-600 mt-0.5">Accept the college matriculation oath to receive your official university student registration number.</p>
                        </div>

                        <form method="post" action="{{ route('admissions.application.enrol', $application) }}" class="space-y-4">
                            @csrf
                            <label class="flex items-start gap-2 text-xs text-slate-700 cursor-pointer">
                                <input type="checkbox" name="enrolment_declaration" value="1" checked class="mt-0.5 rounded text-[#0A3E50] focus:ring-[#0A3E50]" required>
                                <span>I accept my admission to MEMA College and agree to abide by all academic regulations and student code of conduct.</span>
                            </label>

                            <button type="submit" class="px-6 py-2.5 rounded-xl portal-btn-primary font-extrabold text-xs shadow-sm flex items-center gap-1.5">
                                <i data-lucide="user-check" class="w-4 h-4 text-[#E67E22]"></i> Complete Enrolment &amp; Issue Registration No.
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Enrolled Confirmation --}}
                @if($application->status === 'ENROLLED')
                    @php($student = $application->conversion?->student)
                    <div class="bg-white border border-emerald-300 rounded-2xl p-6 shadow-sm bg-emerald-50/30 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold shadow-2xs">
                                <i data-lucide="check-circle" class="w-7 h-7 text-emerald-700"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-extrabold text-emerald-950">You are enrolled</h2>
                                <p class="text-xs text-slate-600 mt-0.5">Your official student profile and LMS portal credentials are active.</p>
                            </div>
                        </div>

                        <div class="p-4 bg-white rounded-xl border border-emerald-200 text-xs flex justify-between items-center">
                            <div>
                                <span class="text-slate-500 block">Student Registration Number</span>
                                <span class="font-mono text-lg font-extrabold text-[#0A3E50]">
                                    {{ $student?->admission_number ?? $application->conversion?->student_number ?? 'CS/001/2026' }}
                                </span>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">ACTIVE STUDENT</span>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Right Side: Timeline & Status History (1 col) --}}
        <div class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
                <div class="bg-[#0A3E50] text-white px-5 py-3.5 flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4 text-[#E67E22]"></i>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-white">Application Timeline</h2>
                </div>
                <div class="p-5 divide-y divide-slate-100">
                    @forelse($application->histories->sortByDesc('created_at') as $history)
                        <div class="py-3 text-xs first:pt-0 last:pb-0">
                            <div class="flex justify-between items-center">
                                <span class="font-extrabold text-[#0A3E50]">{{ str_replace('_', ' ', $history->to_status) }}</span>
                                <span class="font-mono text-[10.5px] text-slate-400">{{ $history->created_at ? (is_string($history->created_at) ? $history->created_at : $history->created_at->format('d M, H:i')) : '—' }}</span>
                            </div>
                            <p class="text-[11.5px] text-slate-600 mt-1 leading-snug">{{ $history->note ?? $history->reason_code }}</p>
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-400 text-xs">
                            Application initiated and recorded.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endif

    <!-- All Applicants & Applications Hub Table (Always Visible for Exploration / Admin / Switcher) -->
    <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs mt-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-extrabold text-[#0A3E50]">All Active Applicant Dossiers</h2>
                <p class="text-xs text-slate-500 mt-0.5">Click any applicant to switch and inspect their complete application lifecycle, verification status, and offer details.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-[#0A3E50]/10 text-[#0A3E50]">
                {{ $allApplications->count() }} Total Applications
            </span>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-extrabold border-b border-slate-200">
                        <th class="py-3 px-4">Candidate Name</th>
                        <th class="py-3 px-4">Programme</th>
                        <th class="py-3 px-4">Application Reference</th>
                        <th class="py-3 px-4">Offer Ref</th>
                        <th class="py-3 px-4 text-center">Lifecycle Status</th>
                        <th class="py-3 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($allApplications as $appRow)
                        <tr class="hover:bg-slate-50/80 transition-colors @if($application && $application->id === $appRow->id) bg-teal-50/50 @endif">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-7 h-7 rounded-lg bg-[#0A3E50] text-white font-bold text-xs flex items-center justify-center">
                                        {{ strtoupper(substr($appRow->applicant?->user?->first_name ?: 'A', 0, 1)) }}
                                    </span>
                                    <div>
                                        <strong class="text-slate-900 text-xs block">{{ $appRow->applicant?->user?->name ?? 'Candidate' }}</strong>
                                        <span class="text-[10.5px] text-slate-400 font-mono">{{ $appRow->applicant?->applicant_number }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <strong class="text-[#0A3E50]">{{ $appRow->offering?->course?->name ?? 'Course' }}</strong>
                                <span class="block text-[10.5px] text-slate-500">{{ $appRow->offering?->intake?->name ?? 'September 2026' }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-700">
                                {{ $appRow->application_number }}
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-600">
                                {{ $appRow->offer?->offer_number ?? '—' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10.5px] font-bold
                                    @if(in_array($appRow->status, ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])) bg-emerald-50 text-emerald-800 border border-emerald-200
                                    @elseif(in_array($appRow->status, ['UNDER_REVIEW', 'SHORTLISTED', 'APPROVAL_PENDING', 'SUBMITTED'])) bg-blue-50 text-blue-800 border border-blue-200
                                    @else bg-amber-50 text-amber-800 border border-amber-200 @endif">
                                    {{ str_replace('_', ' ', $appRow->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('admissions.portal') }}?application_id={{ $appRow->id }}" class="px-3 py-1.5 rounded-lg portal-btn-primary font-extrabold text-[11px] shadow-2xs inline-flex items-center gap-1">
                                    <span>Open Portal</span> &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-400">
                                No applicant dossiers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Published Programmes Explorer -->
    <section class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs mt-6">
        <div class="flex justify-between items-center pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-extrabold text-[#0A3E50]">Published Academic Programmes &amp; Offerings</h2>
                <p class="text-xs text-slate-500 mt-0.5">Explore approved intake curricula and start a new admission application.</p>
            </div>
            <a href="{{ route('admissions.catalogue') }}" class="text-xs font-extrabold text-[#0A3E50] hover:underline">View Full Catalogue &rarr;</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
            @foreach($openOfferings as $offering)
                <div class="bg-slate-50/70 border border-slate-200 rounded-xl p-4 flex flex-col justify-between hover:border-[#0A3E50] transition-all shadow-2xs">
                    <div>
                        <span class="font-mono text-[10px] font-bold text-slate-500 uppercase">{{ $offering->course?->code }}</span>
                        <h3 class="font-extrabold text-sm text-[#0A3E50] mt-0.5 leading-snug">{{ $offering->course?->name }}</h3>
                        <p class="text-[11px] text-slate-500 mt-1">{{ $offering->intake?->name ?? 'September 2026' }}</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-200 flex justify-between items-center">
                        <span class="font-bold text-xs text-slate-700">Fee: KES {{ number_format((float)$offering->application_fee, 0) }}</span>
                        <a href="{{ route('admissions.apply', $offering) }}" class="px-3 py-1 rounded-lg portal-btn-accent font-extrabold text-[11px] shadow-2xs">
                            Apply Now
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

</div>
@endsection
