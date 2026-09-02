@extends('layouts.app')

@section('title', 'My Application Portal')
@section('section', 'Admissions Portal')

@section('content')
<div class="mema-dashboard-container py-2 font-quicksand">

@if(!$application)
    <div class="bg-white border border-slate-200 rounded-2xl p-10 text-center shadow-xs max-w-xl mx-auto my-8 space-y-4">
        <div class="w-16 h-16 bg-[#0A3E50]/10 text-[#0A3E50] rounded-2xl flex items-center justify-center mx-auto shadow-2xs">
            <i data-lucide="compass" class="w-8 h-8 text-[#0A3E50]"></i>
        </div>
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">No Active Application Found</h1>
            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto leading-relaxed">
                You do not have an open application yet. Explore our published academic programmes and submit your application online.
            </p>
        </div>
        <div class="pt-2">
            <a href="{{ route('admissions.catalogue') }}" class="px-6 py-2.5 rounded-xl bg-[#0A3E50] hover:bg-[#08303e] font-extrabold text-xs transition-all shadow-md inline-flex items-center justify-center gap-2 text-white">
                <i data-lucide="book-open" class="w-4 h-4 text-[#E67E22]"></i>
                <span>Explore Programme Catalogue</span>
            </a>
        </div>
    </div>
@else
    @php($paid = $application->isPaid())
    @php($canEdit = in_array($application->status, ['DRAFT', 'RETURNED_FOR_CORRECTION', 'INFO_REQUESTED']))

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
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-2 tracking-tight">{{ $application->offering->course->name }}</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                {{ $application->offering->intake->name }} · {{ $application->offering->campus }} ({{ $application->offering->study_mode }})
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @if(in_array($application->status, ['ADMITTED', 'ACCEPTED', 'READY_TO_ENROL', 'ENROLLED']))
                <a href="{{ route('admissions.application.letter', $application) }}" target="_blank" class="px-4 py-2 rounded-xl bg-[#0A3E50] hover:bg-[#072c39] font-extrabold text-xs text-white transition-all shadow-md inline-flex items-center gap-1.5">
                    <i data-lucide="printer" class="w-4 h-4 text-[#E67E22]"></i>
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
            <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Applicant ID</div>
            <div class="text-lg font-extrabold text-[#0A3E50] mt-2 mb-1 leading-none font-mono">{{ $application->applicant->applicant_number }}</div>
            <p class="text-[11px] text-slate-500 mb-2">Authenticated Profile</p>
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
                {{ $paid ? 'KES 1,000 Paid' : 'KES 1,000 Due' }}
            </div>
            <p class="text-[11px] text-slate-500 mb-2">{{ $paid ? 'Settled via M-Pesa' : 'Payment required to submit' }}</p>
            <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold @if($paid) text-emerald-800 bg-emerald-50 border border-emerald-200 @else text-amber-800 bg-amber-50 border border-amber-200 @endif">
                {{ $paid ? 'Confirmed' : 'Pending Payment' }}
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Current Stage</div>
            <div class="text-base font-extrabold text-[#0A3E50] mt-2 mb-1 leading-none">
                @if($application->status === 'DRAFT') Draft in Progress
                @elseif($application->status === 'SUBMITTED') Under Review
                @elseif($application->status === 'ADMITTED') Offer Dispatched
                @elseif($application->status === 'ENROLLED') Registered Student
                @else Processing @endif
            </div>
            <p class="text-[11px] text-slate-500 mb-2">Updated {{ $application->updated_at->diffForHumans() }}</p>
            <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold text-slate-700 bg-slate-100 border border-slate-200">Live Stage</span>
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
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $application->applicant->date_of_birth ? (is_string($application->applicant->date_of_birth) ? $application->applicant->date_of_birth : $application->applicant->date_of_birth->format('Y-m-d')) : '') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]" required>
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
                            <input name="nationality" value="{{ old('nationality', $application->applicant->nationality ?? 'Kenyan') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]" required>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">County / Region *</label>
                            <input name="county" value="{{ old('county', $application->applicant->county) }}" placeholder="e.g. Nairobi" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]" required>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Identity Document Type *</label>
                            <select name="identity_type" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 bg-white focus:outline-none focus:border-[#0A3E50]" required>
                                <option value="national_id" @if(old('identity_type', $application->applicant->identity_type) === 'national_id') selected @endif>National ID Card</option>
                                <option value="birth_certificate" @if(old('identity_type', $application->applicant->identity_type) === 'birth_certificate') selected @endif>Birth Certificate</option>
                                <option value="passport" @if(old('identity_type', $application->applicant->identity_type) === 'passport') selected @endif>Passport</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Identity / Document Number *</label>
                            <input name="identity_number" value="{{ old('identity_number', $application->applicant->identity_number) }}" placeholder="e.g. 38472910" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-900 focus:outline-none focus:border-[#0A3E50]" required>
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

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-[#0A3E50] hover:bg-[#072c39] text-white font-extrabold text-xs transition-colors shadow-sm">
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

                        <button type="submit" class="w-full py-2.5 rounded-xl bg-[#0A3E50] hover:bg-[#072c39] text-white font-extrabold text-xs transition-colors shadow-sm flex items-center justify-center gap-1.5">
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

                {{-- Kenyan Executive World-Class Payment Gateway Hub --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-5" id="payment-gateway-container">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <div>
                            <div class="text-[11px] font-bold text-[#E67E22] uppercase tracking-wider">Step 4 of 4 • Executive Settlement</div>
                            <h2 class="text-sm font-extrabold text-[#0A3E50]">Official Application Fee Checkout (KES 1,000)</h2>
                        </div>
                        <span class="inline-flex items-center gap-1 px-3 py-0.5 rounded-full text-xs font-bold @if($paid) bg-emerald-50 text-emerald-800 border border-emerald-200 @else bg-amber-50 text-amber-800 border border-amber-200 @endif">
                            <span class="w-1.5 h-1.5 rounded-full @if($paid) bg-emerald-500 @else bg-amber-500 animate-ping @endif"></span>
                            {{ $paid ? 'CONFIRMED & SETTLED' : 'PAYMENT DUE' }}
                        </span>
                    </div>

                    @if(!$paid)
                        {{-- Payment Channel Selector Tabs --}}
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2" id="payment-channel-tabs">
                            <button type="button" onclick="switchPayChannel('stk')" id="tab-stk" class="pay-tab active p-3 rounded-xl border border-[#1E8449] bg-emerald-50/70 text-center transition-all cursor-pointer shadow-2xs">
                                <i data-lucide="smartphone" class="w-5 h-5 mx-auto text-[#1E8449] mb-1"></i>
                                <span class="block text-[11px] font-extrabold text-slate-800 leading-tight">STK Push</span>
                                <span class="text-[9.5px] font-bold text-[#1E8449]">Instant Prompt</span>
                            </button>

                            <button type="button" onclick="switchPayChannel('paybill')" id="tab-paybill" class="pay-tab p-3 rounded-xl border border-slate-200 bg-white text-center hover:bg-slate-50 transition-all cursor-pointer shadow-2xs">
                                <i data-lucide="building-2" class="w-5 h-5 mx-auto text-[#004F9E] mb-1"></i>
                                <span class="block text-[11px] font-extrabold text-slate-800 leading-tight">KCB Paybill</span>
                                <span class="text-[9.5px] font-bold text-slate-500">522 522</span>
                            </button>

                            <button type="button" onclick="switchPayChannel('pochi')" id="tab-pochi" class="pay-tab p-3 rounded-xl border border-slate-200 bg-white text-center hover:bg-slate-50 transition-all cursor-pointer shadow-2xs">
                                <i data-lucide="wallet" class="w-5 h-5 mx-auto text-[#E67E22] mb-1"></i>
                                <span class="block text-[11px] font-extrabold text-slate-800 leading-tight">Pochi Biashara</span>
                                <span class="text-[9.5px] font-bold text-slate-500">0113636154</span>
                            </button>

                            <button type="button" onclick="switchPayChannel('till')" id="tab-till" class="pay-tab p-3 rounded-xl border border-slate-200 bg-white text-center hover:bg-slate-50 transition-all cursor-pointer shadow-2xs">
                                <i data-lucide="shopping-bag" class="w-5 h-5 mx-auto text-teal-700 mb-1"></i>
                                <span class="block text-[11px] font-extrabold text-slate-800 leading-tight">Buy Goods / Till</span>
                                <span class="text-[9.5px] font-bold text-slate-500">0113636154</span>
                            </button>

                            <button type="button" onclick="switchPayChannel('card')" id="tab-card" class="pay-tab p-3 rounded-xl border border-slate-200 bg-white text-center hover:bg-slate-50 transition-all cursor-pointer shadow-2xs">
                                <i data-lucide="credit-card" class="w-5 h-5 mx-auto text-purple-700 mb-1"></i>
                                <span class="block text-[11px] font-extrabold text-slate-800 leading-tight">Card / Stripe</span>
                                <span class="text-[9.5px] font-bold text-slate-500">Visa • MC</span>
                            </button>
                        </div>

                        {{-- Channel 1: STK Push (Default) --}}
                        <div id="channel-content-stk" class="pay-content space-y-4 bg-emerald-50/40 p-5 rounded-xl border border-emerald-200">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-extrabold text-xs text-slate-900 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-[#1E8449]"></span> Safaricom M-Pesa Instant STK Push
                                    </h3>
                                    <p class="text-[11.5px] text-slate-600 mt-0.5">
                                        Enter your Safaricom phone number. A PIN prompt will pop up on your screen instantly.
                                    </p>
                                </div>
                                <span class="font-mono font-bold text-xs text-emerald-800 bg-white px-2 py-0.5 rounded border border-emerald-200">KES 1,000</span>
                            </div>

                            <form method="post" action="{{ route('admissions.application.payment', $application) }}" onsubmit="showStkTriggerAnimation(event, this)">
                                @csrf
                                <input type="hidden" name="channel" value="mpesa">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-bold text-slate-700 mb-1">M-Pesa Mobile Number *</label>
                                        <div class="relative">
                                            <input type="tel" name="phone_number" value="0113636154" id="stk-phone-input" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono font-bold text-slate-900 focus:outline-none focus:border-[#1E8449] pl-10 shadow-2xs">
                                            <i data-lucide="phone" class="w-4 h-4 text-emerald-600 absolute left-3 top-2.5"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="submit" id="stk-submit-btn" class="w-full py-2 px-4 rounded-lg bg-[#1E8449] hover:bg-[#166534] text-white font-extrabold text-xs transition-all shadow-md flex items-center justify-center gap-1.5">
                                            <i data-lucide="send" class="w-3.5 h-3.5"></i> Send STK Prompt
                                        </button>
                                    </div>
                                </div>
                                <div id="stk-waiting-indicator" class="hidden mt-3 p-3 bg-white rounded-lg border border-emerald-300 text-xs text-emerald-900 flex items-center gap-3">
                                    <div class="w-4 h-4 border-2 border-[#1E8449] border-t-transparent rounded-full animate-spin"></div>
                                    <span>STK push dispatched to <strong>0113636154</strong>. Please enter your M-Pesa PIN on your phone to complete...</span>
                                </div>
                            </form>
                        </div>

                        {{-- Channel 2: KCB Paybill 522 522 / Account 0113636154 --}}
                        <div id="channel-content-paybill" class="pay-content hidden space-y-4 bg-blue-50/40 p-5 rounded-xl border border-blue-200">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-extrabold text-xs text-slate-900 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-[#004F9E]"></span> KCB Paybill Gateway
                                    </h3>
                                    <p class="text-[11.5px] text-slate-600 mt-0.5">
                                        Pay via M-Pesa Paybill using the official institutional collection credentials below:
                                    </p>
                                </div>
                                <span class="font-mono font-bold text-xs text-blue-900 bg-white px-2 py-0.5 rounded border border-blue-200">KCB Bank</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="bg-white p-3.5 rounded-lg border border-slate-200 flex justify-between items-center shadow-2xs">
                                    <div>
                                        <span class="block text-[10px] text-slate-400 font-bold uppercase">Business Number (Paybill)</span>
                                        <span class="font-mono text-base font-extrabold text-[#004F9E]">522 522</span>
                                    </div>
                                    <button type="button" onclick="copyToClip('522522', this)" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10.5px] font-bold transition-colors">Copy</button>
                                </div>
                                <div class="bg-white p-3.5 rounded-lg border border-slate-200 flex justify-between items-center shadow-2xs">
                                    <div>
                                        <span class="block text-[10px] text-slate-400 font-bold uppercase">Account Number</span>
                                        <span class="font-mono text-base font-extrabold text-[#004F9E]">0113636154</span>
                                    </div>
                                    <button type="button" onclick="copyToClip('0113636154', this)" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10.5px] font-bold transition-colors">Copy</button>
                                </div>
                            </div>

                            <div class="bg-white p-3 rounded-lg border border-slate-200 text-xs text-slate-600 space-y-1">
                                <strong class="text-slate-800 block font-bold">Manual USSD Payment Steps:</strong>
                                <p class="text-[11.5px]">1. Go to M-Pesa &rarr; Lipa na M-Pesa &rarr; <strong>Pay Bill</strong>.<br>
                                2. Enter Business No: <strong>522522</strong>.<br>
                                3. Enter Account No: <strong>0113636154</strong> (or Ref: <strong>{{ $application->application_number }}</strong>).<br>
                                4. Enter Amount: <strong>1000</strong> &rarr; Enter PIN &rarr; Send.</p>
                            </div>

                            <form method="post" action="{{ route('admissions.application.payment', $application) }}">
                                @csrf
                                <input type="hidden" name="channel" value="paybill">
                                <button type="submit" class="w-full py-2.5 rounded-lg bg-[#004F9E] hover:bg-[#003870] text-white font-extrabold text-xs transition-colors shadow-md flex items-center justify-center gap-2">
                                    <i data-lucide="check-circle-2" class="w-4 h-4"></i> I Have Paid via KCB Paybill (Confirm Receipt)
                                </button>
                            </form>
                        </div>

                        {{-- Channel 3: Pochi la Biashara 0113636154 --}}
                        <div id="channel-content-pochi" class="pay-content hidden space-y-4 bg-amber-50/40 p-5 rounded-xl border border-amber-200">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-extrabold text-xs text-slate-900 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-[#E67E22]"></span> Pochi la Biashara Direct Transfer
                                    </h3>
                                    <p class="text-[11.5px] text-slate-600 mt-0.5">
                                        Send application fee directly to the university admissions Pochi la Biashara mobile wallet:
                                    </p>
                                </div>
                                <span class="font-mono font-bold text-xs text-amber-900 bg-white px-2 py-0.5 rounded border border-amber-200">Pochi la Biashara</span>
                            </div>

                            <div class="bg-white p-4 rounded-lg border border-slate-200 flex justify-between items-center shadow-2xs">
                                <div>
                                    <span class="block text-[10px] text-slate-400 font-bold uppercase">Pochi Mobile Number</span>
                                    <span class="font-mono text-lg font-extrabold text-[#E67E22]">0113636154</span>
                                    <span class="block text-[10.5px] text-slate-500 mt-0.5">Recipient: MEMA Admissions &amp; Enrolments</span>
                                </div>
                                <button type="button" onclick="copyToClip('0113636154', this)" class="px-3 py-1.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">Copy Number</button>
                            </div>

                            <div class="bg-white p-3 rounded-lg border border-slate-200 text-xs text-slate-600 space-y-1">
                                <strong class="text-slate-800 block font-bold">Steps to Pay via Pochi:</strong>
                                <p class="text-[11.5px]">1. Dial <strong>*334#</strong> or open M-Pesa App.<br>
                                2. Select <strong>Lipa na M-Pesa</strong> &rarr; <strong>Pochi la Biashara</strong> &rarr; <strong>Send Money</strong>.<br>
                                3. Enter Phone No: <strong>0113636154</strong> &rarr; Amount: <strong>1000</strong> &rarr; PIN &rarr; Confirm.</p>
                            </div>

                            <form method="post" action="{{ route('admissions.application.payment', $application) }}">
                                @csrf
                                <input type="hidden" name="channel" value="pochi">
                                <button type="submit" class="w-full py-2.5 rounded-lg bg-[#E67E22] hover:bg-[#d35400] text-white font-extrabold text-xs transition-colors shadow-md flex items-center justify-center gap-2">
                                    <i data-lucide="check-circle-2" class="w-4 h-4"></i> I Have Sent via Pochi la Biashara (Confirm Settlement)
                                </button>
                            </form>
                        </div>

                        {{-- Channel 4: Buy Goods / Till 0113636154 --}}
                        <div id="channel-content-till" class="pay-content hidden space-y-4 bg-teal-50/40 p-5 rounded-xl border border-teal-200">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-extrabold text-xs text-slate-900 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-teal-700"></span> Lipa na M-Pesa Buy Goods (Till)
                                    </h3>
                                    <p class="text-[11.5px] text-slate-600 mt-0.5">
                                        Pay via merchant till at zero transaction cost:
                                    </p>
                                </div>
                                <span class="font-mono font-bold text-xs text-teal-900 bg-white px-2 py-0.5 rounded border border-teal-200">Buy Goods</span>
                            </div>

                            <div class="bg-white p-4 rounded-lg border border-slate-200 flex justify-between items-center shadow-2xs">
                                <div>
                                    <span class="block text-[10px] text-slate-400 font-bold uppercase">Till Number</span>
                                    <span class="font-mono text-lg font-extrabold text-teal-800">0113636154</span>
                                    <span class="block text-[10.5px] text-slate-500 mt-0.5">Merchant: MEMA College Collections</span>
                                </div>
                                <button type="button" onclick="copyToClip('0113636154', this)" class="px-3 py-1.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">Copy Till</button>
                            </div>

                            <form method="post" action="{{ route('admissions.application.payment', $application) }}">
                                @csrf
                                <input type="hidden" name="channel" value="till">
                                <button type="submit" class="w-full py-2.5 rounded-lg bg-teal-800 hover:bg-teal-900 text-white font-extrabold text-xs transition-colors shadow-md flex items-center justify-center gap-2">
                                    <i data-lucide="check-circle-2" class="w-4 h-4"></i> I Have Paid via Till 0113636154 (Confirm Payment)
                                </button>
                            </form>
                        </div>

                        {{-- Channel 5: Card / Stripe 3D-Secure --}}
                        <div id="channel-content-card" class="pay-content hidden space-y-4 bg-purple-50/30 p-5 rounded-xl border border-purple-200">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-extrabold text-xs text-slate-900 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-purple-700"></span> Global Debit / Credit Card (Stripe 3D-Secure 2.0)
                                    </h3>
                                    <p class="text-[11.5px] text-slate-600 mt-0.5">
                                        International Visa, Mastercard, and American Express supported with instant automated clearance.
                                    </p>
                                </div>
                                <span class="font-mono font-bold text-xs text-purple-900 bg-white px-2 py-0.5 rounded border border-purple-200">Stripe Verified</span>
                            </div>

                            <form method="post" action="{{ route('admissions.application.payment', $application) }}" class="space-y-3">
                                @csrf
                                <input type="hidden" name="channel" value="stripe">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Cardholder Name *</label>
                                    <input type="text" placeholder="John Doe" value="{{ old('card_name', $application->applicant->user->name ?? 'Applicant') }}" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-medium text-slate-900 focus:outline-none focus:border-purple-600 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Card Number *</label>
                                    <input type="text" placeholder="4242 •••• •••• 4242" value="4242 4242 4242 4242" maxlength="19" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono font-bold text-slate-900 focus:outline-none focus:border-purple-600 shadow-2xs">
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Expiry (MM/YY) *</label>
                                        <input type="text" placeholder="12/28" value="12/28" maxlength="5" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono text-center font-bold text-slate-900 focus:outline-none focus:border-purple-600 shadow-2xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">CVV / CVC *</label>
                                        <input type="password" placeholder="•••" value="123" maxlength="4" required class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono text-center font-bold text-slate-900 focus:outline-none focus:border-purple-600 shadow-2xs">
                                    </div>
                                </div>

                                <button type="submit" class="w-full py-2.5 rounded-lg bg-purple-900 hover:bg-purple-950 text-white font-extrabold text-xs transition-colors shadow-md flex items-center justify-center gap-2">
                                    <i data-lucide="lock" class="w-4 h-4 text-purple-300"></i> Pay KES 1,000 via Stripe Secure Card Gateway
                                </button>
                            </form>
                        </div>
                    @else
                        {{-- Confirmed Receipt Card --}}
                        <div class="p-5 rounded-xl bg-emerald-50 border border-emerald-300 text-xs text-emerald-950 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 shadow-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold shadow-2xs">
                                    <i data-lucide="check-check" class="w-5 h-5 text-emerald-700"></i>
                                </div>
                                <div>
                                    <strong class="font-extrabold text-sm block">Payment Cleared &amp; Settled</strong>
                                    <div class="font-mono text-[11px] text-emerald-800 mt-0.5">Official Receipt: {{ $application->payments()->where('status', 'PAID')->value('receipt_number') ?? 'MEMA-RCPT-2026' }}</div>
                                    <span class="text-[10.5px] text-slate-600">Channel: {{ strtoupper($application->payments()->where('status', 'PAID')->value('channel') ?? 'M-PESA') }} • KES 1,000.00</span>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold text-emerald-900 bg-emerald-100 border border-emerald-300">CLEARED</span>
                        </div>
                    @endif
                </div>

                <script>
                    function switchPayChannel(channel) {
                        document.querySelectorAll('.pay-tab').forEach(b => {
                            b.classList.remove('active', 'border-[#1E8449]', 'bg-emerald-50/70');
                            b.classList.add('border-slate-200', 'bg-white');
                        });
                        document.querySelectorAll('.pay-content').forEach(c => c.classList.add('hidden'));

                        const activeTab = document.getElementById('tab-' + channel);
                        const activeContent = document.getElementById('channel-content-' + channel);

                        if (activeTab) {
                            activeTab.classList.add('active', 'border-[#1E8449]', 'bg-emerald-50/70');
                            activeTab.classList.remove('border-slate-200', 'bg-white');
                        }
                        if (activeContent) {
                            activeContent.classList.remove('hidden');
                        }
                    }

                    function copyToClip(text, btn) {
                        navigator.clipboard.writeText(text);
                        const orig = btn.textContent;
                        btn.textContent = 'Copied!';
                        btn.classList.add('bg-emerald-100', 'text-emerald-800');
                        setTimeout(() => {
                            btn.textContent = orig;
                            btn.classList.remove('bg-emerald-100', 'text-emerald-800');
                        }, 2000);
                    }

                    function showStkTriggerAnimation(e, form) {
                        const ind = document.getElementById('stk-waiting-indicator');
                        const btn = document.getElementById('stk-submit-btn');
                        if (ind) ind.classList.remove('hidden');
                        if (btn) {
                            btn.disabled = true;
                            btn.classList.add('opacity-75');
                            btn.innerHTML = '<span class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span> Dispatched STK Prompt...';
                        }
                    }
                </script>

                {{-- Final Submit Button --}}
                <form method="post" action="{{ route('admissions.application.submit', $application) }}">
                    @csrf
                    <button type="submit" class="w-full py-3.5 rounded-xl bg-[#0A3E50] hover:bg-[#072c39] text-white font-extrabold text-sm transition-all shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2" @disabled(!$paid || $application->completion_percent < 100)>
                        Submit Application for Formal Review <i data-lucide="arrow-right" class="w-4 h-4 text-[#E67E22]"></i>
                    </button>
                    @if(!$paid || $application->completion_percent < 100)
                        <p class="text-center text-[11px] text-slate-400 mt-2">
                            Please complete personal information, upload at least one document, and confirm payment to submit.
                        </p>
                    @endif
                </form>
            @else
                {{-- Post-submission Summary View --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                        <h2 class="text-sm font-extrabold text-[#0A3E50] uppercase tracking-wide">Submitted Application Dossier</h2>
                        <span class="font-mono text-xs text-slate-600 font-bold">{{ $application->application_number }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-slate-500 block">Degree Programme</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $application->offering->course->name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Intake &amp; Campus</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $application->offering->intake->name }} · {{ $application->offering->campus }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Submission Date</span>
                            <span class="font-bold text-slate-900">{{ $application->submitted_at ? (is_string($application->submitted_at) ? $application->submitted_at : $application->submitted_at->format('d M, Y H:i')) : ($application->updated_at ? (is_string($application->updated_at) ? $application->updated_at : $application->updated_at->format('d M, Y')) : '—') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Official Submission Receipt</span>
                            <span class="font-mono font-bold text-emerald-800">{{ $application->submission_receipt_number ?? 'MC/SUB/2026/001' }}</span>
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
                                <p class="text-xs text-slate-600 mt-0.5">Please review your offer and confirm acceptance before {{ $application->offering->intake->acceptance_deadline ? date('d M, Y', strtotime($application->offering->intake->acceptance_deadline)) : '30 September 2026' }}.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-2 flex-wrap">
                            <form method="post" action="{{ route('admissions.application.respond', $application) }}" class="inline">
                                @csrf
                                <input type="hidden" name="response" value="ACCEPTED">
                                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#1E8449] hover:bg-[#166534] text-white font-extrabold text-xs transition-colors shadow-sm flex items-center gap-1.5">
                                    <i data-lucide="check" class="w-4 h-4"></i> Accept Admission Offer
                                </button>
                            </form>

                            <form method="post" action="{{ route('admissions.application.respond', $application) }}" class="inline" onsubmit="return confirm('Are you sure you want to decline this admission offer?');">
                                @csrf
                                <input type="hidden" name="response" value="DECLINED">
                                <button type="submit" class="px-4 py-2.5 rounded-xl border border-red-300 text-red-700 hover:bg-red-50 font-bold text-xs transition-colors">
                                    Decline Offer
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

                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#0A3E50] hover:bg-[#08303e] text-white font-extrabold text-xs transition-colors shadow-sm flex items-center gap-1.5">
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
