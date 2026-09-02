@extends('layouts.app')

@section('title', 'Offer Workspace')
@section('section', 'Admissions')

@section('content')
<div class="mema-dashboard-container py-2">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Admission Offer Letters &amp; Acceptance Registry</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">Generate cryptographic QR-authenticated admission letters, monitor offer dispatch delivery, and track acceptance deadlines</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-1.5 rounded-md border border-slate-300 text-slate-400 font-bold text-xs cursor-not-allowed shadow-2xs" disabled
                    title="No PDF renderer is installed. Open a letter and print it to PDF from the browser.">
                Batch Generate PDFs
            </button>
        </div>
    </div>

    {{-- Top 4 KPI Metrics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Total Offers Issued</div>
            <div class="text-3xl font-extrabold text-[#0A3E50] mt-2 mb-1.5 leading-none">{{ $stats['totalOffersIssued'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Formal admission packets.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-blue-800 bg-blue-50 border border-blue-200">Official Letters</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Accepted &amp; Enrolling</div>
            <div class="text-3xl font-extrabold text-emerald-700 mt-2 mb-1.5 leading-none">{{ $stats['acceptedOffers'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Signed declarations received.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200">71.6% Yield Rate</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Pending Acceptance</div>
            <div class="text-3xl font-extrabold text-amber-700 mt-2 mb-1.5 leading-none">{{ $stats['pendingResponse'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Within active acceptance window.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200">Deadline: Sept 30</span></div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs transition-all hover:border-[#0A3E50]">
            <div class="text-xs font-bold text-slate-800 uppercase tracking-wide">Declined / Revoked</div>
            <div class="text-3xl font-extrabold text-red-700 mt-2 mb-1.5 leading-none">{{ $stats['declinedRevoked'] }}</div>
            <p class="text-xs text-slate-500 mb-3 leading-snug">Slots returned to waitlist pool.</p>
            <div><span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold text-red-800 bg-red-50 border border-red-200">Slot Recovery Active</span></div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    @include('admissions.admin.workspaces.partials.toolbar', [
        'rows' => $offersList,
        'noun' => 'issued admission offers',
        'search' => 'Search applicant, offer no...',
        'selects' => [
            ['name' => 'status', 'label' => 'All Offer Statuses', 'options' => $statuses],
        ],
    ])

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#0A3E50] text-white">
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Offer Number &amp; App No</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Admitted Candidate</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Admitted Programme</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Issued Date</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Acceptance Deadline</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">QR Verification Token</th>
                        <th class="py-3 px-4 font-bold tracking-wider border-r border-white/15 uppercase text-[11px]" style="color:#ffffff !important;">Status</th>
                        <th class="py-3 px-4 font-bold tracking-wider text-center uppercase text-[11px] w-28" style="color:#ffffff !important;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($offersList as $offer)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-[11px] font-bold text-purple-900 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200">{{ $offer['offer_number'] }}</span>
                                <div class="font-mono text-[11px] text-slate-500 mt-1">{{ $offer['app_no'] }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $offer['applicant_name'] }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $offer['programme'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600">{{ $offer['issued_at'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-[11px] font-bold text-amber-900">{{ $offer['deadline'] }}</td>
                            <td class="py-3.5 px-4 font-mono text-[10.5px] text-[#0A3E50]">{{ $offer['verification_token'] }}</td>
                            <td class="py-3.5 px-4">
                                @if($offer['status'] === 'ACCEPTED')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800">ACCEPTED</span>
                                @elseif(str_contains($offer['status'], 'PENDING'))
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-amber-100 text-amber-800">PENDING RESPONSE</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10.5px] font-bold bg-red-100 text-red-800">DECLINED</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('admissions.application.letter', $offer['application_id']) }}" target="_blank" rel="noopener" class="px-3 py-1 rounded border border-orange-400 text-orange-600 hover:bg-orange-50 font-semibold text-xs transition-colors">
                                    Letter
                                </a>
                            </td>
                        </tr>
                    @empty
                        @include('admissions.admin.workspaces.partials.empty', [
                            'colspan' => 8,
                            'message' => 'No admission offer has been issued yet.',
                            'hint' => 'Offers are generated automatically when an application is admitted.',
                        ])
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('admissions.admin.workspaces.partials.pagination', ['rows' => $offersList])
    </div>
</div>
@endsection
