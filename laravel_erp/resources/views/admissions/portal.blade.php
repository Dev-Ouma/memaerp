@extends('layouts.app')

@section('title', 'My Application')
@section('section', 'Applicant Portal')

@section('content')
<div class="mema-dashboard-container py-2">
@if(!$application)
    <div class="bg-white border border-slate-200 rounded-xl p-10 text-center shadow-xs max-w-xl mx-auto my-8">
        <div class="w-16 h-16 bg-[#0A3E50]/10 text-[#0A3E50] rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="book-open" class="w-8 h-8 text-[#0A3E50]"></i>
        </div>
        <h1 class="text-xl font-bold text-slate-900 mb-2">No Active Application Found</h1>
        <p class="text-xs text-slate-600 mb-6 leading-relaxed max-w-md mx-auto">
            You do not have an open application yet. Explore our published academic programmes and start your application journey with MEMA College.
        </p>
        <a href="{{ route('admissions.catalogue') }}" class="px-6 py-2.5 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] font-bold text-xs transition-colors shadow-sm inline-flex items-center justify-center gap-2" style="color: #ffffff !important; text-decoration: none !important;">
            <i data-lucide="compass" class="w-4 h-4" style="color: #ffffff !important;"></i>
            <span style="color: #ffffff !important;">Explore Programme Catalogue</span>
        </a>
    </div>
@else
    @php($paid = $application->isPaid())
    @php($canEdit = in_array($application->status, ['DRAFT', 'RETURNED_FOR_CORRECTION', 'INFO_REQUESTED']))

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="font-mono text-xs font-bold text-[#0A3E50] bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                    App Ref: {{ $application->application_number }}
                </span>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold
                    @if(in_array($application->status, ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED'])) bg-emerald-100 text-emerald-800 border border-emerald-300
                    @elseif(in_array($application->status, ['UNDER_REVIEW', 'SHORTLISTED', 'APPROVAL_PENDING', 'SUBMITTED'])) bg-blue-100 text-blue-800 border border-blue-300
                    @elseif(in_array($application->status, ['RETURNED_FOR_CORRECTION', 'INFO_REQUESTED', 'WAITLISTED', 'DRAFT'])) bg-amber-100 text-amber-800 border border-amber-300
                    @else bg-red-100 text-red-800 border border-red-300 @endif">
                    {{ str_replace('_', ' ', $application->status) }}
                </span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1 tracking-tight">{{ $application->offering->course->name }}</h1>
            <p class="text-xs text-slate-500 font-medium">
                {{ $application->offering->intake->name }} · {{ $application->offering->campus }} ({{ $application->offering->study_mode }})
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if(in_array($application->status, ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED']))
                <a href="{{ route('admissions.application.letter', $application) }}" target="_blank" class="px-4 py-2 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] font-bold text-xs transition-colors shadow-sm inline-flex items-center gap-1.5" style="color: #ffffff !important; text-decoration: none !important;">
                    <i data-lucide="printer" class="w-4 h-4" style="color: #ffffff !important;"></i>
                    <span style="color: #ffffff !important;">View Official Admission Letter</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Applicant ID</div>
            <div class="text-xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ $application->applicant->applicant_number }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Personal applicant account.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Verified Identity</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Application Progress</div>
            <div class="text-3xl font-extrabold text-purple-800 mt-2 mb-1.5 leading-none">{{ $application->completion_percent }}%</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Required sections completed.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-purple-800 bg-purple-50 border border-purple-200">8-Step Form</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Application Fee</div>
            <div class="text-2xl font-extrabold @if($paid) text-[#1E8449] @else text-amber-700 @endif mt-2 mb-1.5 leading-none">
                {{ $paid ? 'KES 1,000 Paid' : 'KES 1,000 Due' }}
            </div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">{{ $paid ? 'Confirmed & Settled' : 'Payment required to submit' }}</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold @if($paid) text-emerald-800 bg-emerald-50 border border-emerald-200 @else text-amber-800 bg-amber-50 border border-amber-200 @endif">M-Pesa / Bank Slip</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Current Status</div>
            <div class="text-xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">
                @if($application->status === 'DRAFT') Ready to Submit
                @elseif($application->status === 'SUBMITTED') Under Processing
                @elseif($application->status === 'ADMITTED') Offer Received
                @elseif($application->status === 'ENROLLED') Registered Student
                @else In Triage @endif
            </div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Updated {{ $application->updated_at->diffForHumans() }}.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-slate-700 bg-slate-100 border border-slate-200">Live Stage</span></div>
        </div>
    </div>

    {{-- Correction Notice Banner if returned --}}
    @if(in_array($application->status, ['RETURNED_FOR_CORRECTION', 'INFO_REQUESTED']))
        <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 flex items-start gap-3">
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
                <form method="post" action="{{ route('admissions.application.update', $application) }}" data-autosave-form data-application-id="{{ $application->id }}" class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="lock_version" value="{{ $application->lock_version }}">

                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <h2 class="text-xs font-bold text-[#0A3E50] uppercase tracking-wide">1. Personal &amp; Contact Details</h2>
                        <span data-save-status aria-live="polite" class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">All changes saved</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Date of Birth</label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $application->applicant->date_of_birth ? (is_string($application->applicant->date_of_birth) ? $application->applicant->date_of_birth : $application->applicant->date_of_birth->format('Y-m-d')) : '') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0A3E50]" required>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Gender</label>
                            <select name="gender" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-[#0A3E50]" required>
                                <option value="">Select Gender</option>
                                <option value="M" @if(old('gender', $application->form_data['gender'] ?? '') === 'M') selected @endif>Male</option>
                                <option value="F" @if(old('gender', $application->form_data['gender'] ?? '') === 'F') selected @endif>Female</option>
                                <option value="N" @if(old('gender', $application->form_data['gender'] ?? '') === 'N') selected @endif>Prefer not to say</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nationality</label>
                            <input name="nationality" value="{{ old('nationality', $application->applicant->nationality ?? 'Kenyan') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0A3E50]" required>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">County / Region</label>
                            <input name="county" value="{{ old('county', $application->applicant->county) }}" placeholder="e.g. Nairobi" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0A3E50]">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Identity Document Type</label>
                            <select name="identity_type" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-[#0A3E50]">
                                <option value="national_id" @if(old('identity_type', $application->applicant->identity_type) === 'national_id') selected @endif>National ID Card</option>
                                <option value="birth_certificate" @if(old('identity_type', $application->applicant->identity_type) === 'birth_certificate') selected @endif>Birth Certificate</option>
                                <option value="passport" @if(old('identity_type', $application->applicant->identity_type) === 'passport') selected @endif>Passport</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Identity / Document Number</label>
                            <input name="identity_number" value="{{ old('identity_number', $application->applicant->identity_number) }}" placeholder="e.g. 38472910" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0A3E50]" required>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <label class="block font-bold text-slate-700 mb-1">2. Academic Background &amp; Entry Qualifications</label>
                        <textarea name="education" rows="3" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#0A3E50]" placeholder="Enter KCSE index number, mean grade, cluster subject results, or previous post-secondary certificates..." required>{{ old('education', $application->form_data['education'] ?? '') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-2">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Source Channel</label>
                            <select name="source_channel" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-[#0A3E50]">
                                <option value="Website">University Website</option>
                                <option value="Social media">Social Media</option>
                                <option value="School visit">School Outreach / Career Day</option>
                                <option value="Friend or family">Friend / Family Referral</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="flex items-center pt-5">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                <input type="checkbox" name="has_support_need" value="1" @if($application->applicant->has_support_need) checked @endif class="rounded text-[#0A3E50] focus:ring-[#0A3E50]">
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

                    <button type="submit" class="w-full py-2.5 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] text-white font-bold text-xs transition-colors shadow-xs">
                        Save Application Details
                    </button>
                </form>

                {{-- Document Upload Box --}}
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <h2 class="text-xs font-bold text-[#0A3E50] uppercase tracking-wide">3. Supporting Documents Upload</h2>
                        <span class="text-[11px] font-bold text-purple-800 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">{{ $application->documents->count() }} Uploaded</span>
                    </div>

                    <form method="post" enctype="multipart/form-data" action="{{ route('admissions.application.documents', $application) }}" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Document Category</label>
                                <select name="document_type" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-[#0A3E50]">
                                    <option value="identity">National ID / Birth Certificate</option>
                                    <option value="certificate">KCSE / Academic Certificate</option>
                                    <option value="transcript">Official Academic Transcript</option>
                                    <option value="photo">Passport Photograph</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Select File (PDF, JPG, PNG &le; 5MB)</label>
                                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#0A3E50]/10 file:text-[#0A3E50] hover:file:bg-[#0A3E50]/20" required>
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] text-white font-bold text-xs transition-colors shadow-xs">
                            Upload Supporting Document
                        </button>
                    </form>

                    {{-- List of Uploaded Documents --}}
                    @if($application->documents->isNotEmpty())
                        <div class="pt-3 border-t border-slate-100 space-y-2">
                            @foreach($application->documents as $doc)
                                <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded border border-slate-200 text-xs">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="file-check" class="w-4 h-4 text-[#0A3E50]"></i>
                                        <div>
                                            <span class="font-bold text-slate-800">{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}:</span>
                                            <span class="text-slate-600">{{ $doc->original_name }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold @if($doc->verification_status==='VERIFIED') bg-emerald-100 text-emerald-800 @else bg-amber-100 text-amber-800 @endif">{{ $doc->verification_status }}</span>
                                        <a href="{{ route('admissions.document.download', $doc) }}" target="_blank" class="text-[#0A3E50] hover:underline font-semibold text-[11px]">Download</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Fee Payment Card --}}
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <h2 class="text-xs font-bold text-[#0A3E50] uppercase tracking-wide">4. Application Processing Fee</h2>
                        <span class="text-xs font-bold @if($paid) text-emerald-700 @else text-amber-700 @endif">
                            {{ $paid ? 'CONFIRMED' : 'REQUIRED' }}
                        </span>
                    </div>

                    @if(!$paid)
                        <div class="p-3.5 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-900">
                            <strong>Pay KES 1,000 Application Processing Fee</strong>
                            <p class="mt-0.5">Use Safaricom M-Pesa Paybill <strong>880100</strong>, Account Number <strong>{{ $application->application_number }}</strong>, or click below to simulate instant M-Pesa push.</p>
                        </div>

                        <form method="post" action="{{ route('admissions.application.payment', $application) }}">
                            @csrf
                            <input type="hidden" name="channel" value="mpesa">
                            <button type="submit" class="w-full py-2.5 rounded-lg bg-[#1E8449] hover:bg-[#166534] text-white font-bold text-xs transition-colors shadow-xs">
                                Confirm M-Pesa Payment (KES 1,000)
                            </button>
                        </form>
                    @else
                        <div class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 flex items-center justify-between">
                            <div>
                                <strong>Payment Confirmed</strong>
                                <div class="font-mono text-[11px] text-emerald-700 mt-0.5">Receipt: {{ $application->payments()->where('status', 'PAID')->value('receipt_number') ?? 'MEMA-RCPT-2026' }}</div>
                            </div>
                            <span class="font-bold text-emerald-800 text-sm">KES 1,000</span>
                        </div>
                    @endif
                </div>

                {{-- Final Submit Button --}}
                <form method="post" action="{{ route('admissions.application.submit', $application) }}">
                    @csrf
                    <button type="submit" class="w-full py-3 rounded-xl bg-[#1E8449] hover:bg-[#166534] text-white font-extrabold text-sm transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" @disabled(!$paid || $application->completion_percent < 100)>
                        Submit Application for Formal Review &rarr;
                    </button>
                    @if(!$paid || $application->completion_percent < 100)
                        <p class="text-center text-[11px] text-slate-400 mt-2">
                            Complete personal information, upload at least one document, and confirm payment to submit.
                        </p>
                    @endif
                </form>
            @else
                {{-- Post-submission Summary View --}}
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <h2 class="text-xs font-bold text-[#0A3E50] uppercase tracking-wide">Submitted Application Summary</h2>
                        <span class="font-mono text-xs text-slate-500 font-semibold">{{ $application->application_number }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-slate-500 block">Programme</span>
                            <span class="font-bold text-slate-900">{{ $application->offering->course->name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Intake &amp; Campus</span>
                            <span class="font-bold text-slate-900">{{ $application->offering->intake->name }} · {{ $application->offering->campus }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Submission Date</span>
                            <span class="font-bold text-slate-900">{{ $application->submitted_at ? (is_string($application->submitted_at) ? $application->submitted_at : $application->submitted_at->format('d M, Y H:i')) : ($application->updated_at ? (is_string($application->updated_at) ? $application->updated_at : $application->updated_at->format('d M, Y')) : '—') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Submission Receipt</span>
                            <span class="font-mono font-bold text-emerald-800">{{ $application->submission_receipt_number ?? 'MC/SUB/2026/001' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Offer Response Action if ADMITTED --}}
                @if($application->status === 'ADMITTED')
                    <div class="bg-white border-2 border-emerald-400 rounded-xl p-6 shadow-sm space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold">
                                <i data-lucide="award" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-emerald-950">Congratulations! You Have Been Admitted</h2>
                                <p class="text-xs text-slate-600 mt-0.5">Please review your offer and confirm acceptance before {{ $application->offering->intake->acceptance_deadline ? date('d M, Y', strtotime($application->offering->intake->acceptance_deadline)) : '30 September 2026' }}.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <form method="post" action="{{ route('admissions.application.respond', $application) }}" class="inline">
                                @csrf
                                <input type="hidden" name="response" value="ACCEPTED">
                                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1E8449] hover:bg-[#166534] text-white font-bold text-xs transition-colors shadow-xs">
                                    Accept Admission Offer
                                </button>
                            </form>

                            <form method="post" action="{{ route('admissions.application.respond', $application) }}" class="inline" onsubmit="return confirm('Are you sure you want to decline this admission offer?');">
                                @csrf
                                <input type="hidden" name="response" value="DECLINED">
                                <button type="submit" class="px-4 py-2.5 rounded-lg border border-red-300 text-red-700 hover:bg-red-50 font-semibold text-xs transition-colors">
                                    Decline Offer
                                </button>
                            </form>

                            <a href="{{ route('admissions.application.letter', $application) }}" target="_blank" class="px-4 py-2.5 rounded-lg border border-[#0A3E50] text-[#0A3E50] hover:bg-[#0A3E50]/5 font-bold text-xs transition-colors">
                                Download Offer Letter (PDF)
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Enrolment Action if READY_TO_ENROL --}}
                @if($application->status === 'READY_TO_ENROL')
                    <div class="bg-white border-2 border-[#0A3E50] rounded-xl p-6 shadow-sm space-y-4">
                        <div>
                            <h2 class="text-base font-bold text-[#0A3E50]">Complete your enrolment</h2>
                            <p class="text-xs text-slate-600 mt-0.5">Accept the college matriculation oath to receive your official university student registration number.</p>
                        </div>

                        <form method="post" action="{{ route('admissions.application.enrol', $application) }}" class="space-y-3">
                            @csrf
                            <label class="flex items-start gap-2 text-xs text-slate-700 cursor-pointer">
                                <input type="checkbox" name="enrolment_declaration" value="1" checked class="mt-0.5 rounded text-[#0A3E50] focus:ring-[#0A3E50]" required>
                                <span>I accept my admission to MEMA College and agree to abide by all academic regulations and student code of conduct.</span>
                            </label>

                            <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#0A3E50] hover:bg-[#08303e] text-white font-bold text-xs transition-colors shadow-xs">
                                Complete enrolment
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Enrolled Confirmation --}}
                @if($application->status === 'ENROLLED')
                    @php($student = $application->conversion?->student)
                    <div class="bg-white border border-emerald-300 rounded-xl p-6 shadow-sm bg-emerald-50/30 space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold">
                                <i data-lucide="check-circle" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-emerald-950">You are enrolled</h2>
                                <p class="text-xs text-slate-600 mt-0.5">Your student record is active. Welcome to MEMA College!</p>
                            </div>
                        </div>

                        <div class="p-3 bg-white rounded-lg border border-emerald-200 text-xs">
                            <span class="text-slate-500 block">Student Registration Number</span>
                            <span class="font-mono text-base font-extrabold text-[#0A3E50]">
                                {{ $student?->admission_number ?? $application->conversion?->student_number ?? 'CS/001/2026' }}
                            </span>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Right Side: Timeline & Status History (1 col) --}}
        <div class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                <div class="bg-[#0A3E50] text-white px-5 py-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-white">Application Timeline</h2>
                </div>
                <div class="p-5 divide-y divide-slate-100">
                    @forelse($application->histories->sortByDesc('created_at') as $history)
                        <div class="py-2.5 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-[#0A3E50]">{{ str_replace('_', ' ', $history->to_status) }}</span>
                                <span class="font-mono text-[10px] text-slate-400">{{ $history->created_at ? (is_string($history->created_at) ? $history->created_at : $history->created_at->format('d M, H:i')) : '—' }}</span>
                            </div>
                            <p class="text-[11px] text-slate-600 mt-0.5">{{ $history->note ?? $history->reason_code }}</p>
                        </div>
                    @empty
                        <div class="text-center py-4 text-slate-400 text-xs">
                            Application initiated.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endif
</div>
@endsection
