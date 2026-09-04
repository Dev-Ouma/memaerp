<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
    <div>
        <div class="eyebrow">Applicant admission workspace</div>
        <h1 class="heading" style="margin:2px 0 4px;">Your journey to MEMA starts here.</h1>
        <p class="sub" style="margin:0;">Complete your application, confirm payment, track document verification, and accept your offer.</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
        @if($application)
            <a href="{{ route('admissions.portal') }}" class="btn" style="background:#0A3E50;color:#fff;font-size:12px;padding:8px 14px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                <i data-lucide="arrow-right-circle" style="width:14px;height:14px;"></i> Open Full Applicant Portal
            </a>
        @else
            <a href="{{ route('admissions.catalogue') }}" class="btn" style="background:#0A3E50;color:#fff;font-size:12px;padding:8px 14px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                <i data-lucide="compass" style="width:14px;height:14px;"></i> Explore Programmes
            </a>
        @endif
    </div>
</div>

<!-- 5-Stage Interactive Application Pipeline -->
<section class="panel" style="margin-bottom:20px;padding:20px;border-radius:12px;background:#ffffff;box-shadow:0 2px 10px rgba(0,0,0,0.04);">
    <div style="margin-bottom:16px;">
        <h2 style="font-size:15px;color:#0A3E50;margin:0 0 2px;">Admission lifecycle pipeline</h2>
        <small style="color:var(--muted)">Track your real-time stage through the MEMA College onboarding process</small>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;position:relative;">
        @foreach($steps as $s)
            @php
                $isCompleted = $s['status'] === 'completed';
                $isCurrent = $s['status'] === 'current';
                $bg = $isCompleted ? '#e6f4ea' : ($isCurrent ? '#e6f1eb' : '#f8fafc');
                $border = $isCompleted ? '#1E8449' : ($isCurrent ? '#0A3E50' : '#e2e8f0');
                $text = $isCompleted ? '#1E8449' : ($isCurrent ? '#0A3E50' : '#94a3b8');
            @endphp
            <div style="background:{{ $bg }};border:1.5px solid {{ $border }};padding:14px;border-radius:10px;display:flex;flex-direction:column;gap:6px;transition:all 0.2s;">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:11px;font-weight:800;color:{{ $text }};text-transform:uppercase;letter-spacing:0.5px;">
                        Stage {{ $s['step'] }}
                    </span>
                    @if($isCompleted)
                        <i data-lucide="check-circle-2" style="width:16px;height:16px;color:#1E8449;"></i>
                    @elseif($isCurrent)
                        <i data-lucide="circle-dot" style="width:16px;height:16px;color:#0A3E50;"></i>
                    @else
                        <i data-lucide="circle" style="width:16px;height:16px;color:#94a3b8;"></i>
                    @endif
                </div>
                <strong style="font-size:13px;color:{{ $isCurrent || $isCompleted ? '#0A3E50' : '#64748b' }};">{{ $s['title'] }}</strong>
                <small style="font-size:11px;color:#64748b;line-height:1.3;">{{ $s['description'] }}</small>
            </div>
        @endforeach
    </div>
</section>

<div class="cols" style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
    <!-- Main Left Column: Application Details, Documents & Offer -->
    <div>
        <!-- Active Application Overview -->
        <section class="panel" style="margin-bottom:20px;">
            <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                <div>
                    <h2>{{ $application?->offering?->course?->name ?? 'Course Application' }}</h2>
                    <small style="color:var(--muted)">Application Reference: <strong>{{ $application?->application_number ?? 'Draft Application' }}</strong></small>
                </div>
                @if($application)
                    <span class="badge" style="background:#0A3E50;color:#ffffff;font-weight:750;padding:6px 12px;border-radius:6px;">
                        {{ str($application->status)->replace('_', ' ')->title() }}
                    </span>
                @endif
            </div>
            <div class="panel-body" style="padding:16px 20px;">
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));gap:16px;margin-bottom:16px;">
                    <div>
                        <span style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700;">Target Programme</span>
                        <div style="font-size:14px;font-weight:700;color:#0A3E50;">{{ $application?->offering?->course?->name ?? 'Not selected' }}</div>
                    </div>
                    <div>
                        <span style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700;">Intake Session</span>
                        <div style="font-size:14px;font-weight:700;color:#0A3E50;">{{ $application?->offering?->intake?->name ?? 'September 2026' }}</div>
                    </div>
                    <div>
                        <span style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700;">Study Mode</span>
                        <div style="font-size:14px;font-weight:700;color:#0A3E50;">{{ $application?->study_mode ?? 'Full-Time' }}</div>
                    </div>
                </div>

                <!-- Action button -->
                <a class="btn" href="{{ $application ? route('admissions.portal') : route('admissions.catalogue') }}" style="background:#0A3E50;color:#ffffff;font-size:13px;font-weight:700;padding:10px 18px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
                    <i data-lucide="arrow-right-circle" style="width:16px;height:16px;"></i>
                    {{ $application ? 'Continue application & view files' : 'Explore available programmes' }}
                </a>
            </div>
        </section>

        <!-- Official Offer Letter Card (If Offer Issued) -->
        @if($offer)
            <section class="panel" style="margin-bottom:20px;background:linear-gradient(135deg, #e6f4ea 0%, #f0fdf4 100%);border:2px solid #1E8449;border-radius:12px;">
                <div class="panel-head" style="border-bottom:1px solid #c2e7cf;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <i data-lucide="award" style="color:#1E8449;width:24px;height:24px;"></i>
                        <div>
                            <h2 style="color:#1E8449;margin:0;">Official admission offer issued!</h2>
                            <small style="color:#166534;">Offer Reference: <strong>{{ $offer->offer_number }}</strong></small>
                        </div>
                    </div>
                    <span class="badge" style="background:#1E8449;color:#ffffff;font-weight:800;padding:4px 10px;">
                        {{ $offer->status }}
                    </span>
                </div>
                <div class="panel-body" style="padding:16px 20px;">
                    <p style="color:#166534;margin:0 0 14px;font-size:13px;">
                        Congratulations! The Admissions Board has accepted your application to MEMA College. Please download your official admission letter and confirm your acceptance before the expiry deadline.
                    </p>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <a href="{{ route('admissions.application.letter', $application) }}" target="_blank" class="btn" style="background:#1E8449;color:#ffffff;font-size:12px;font-weight:700;padding:8px 14px;border-radius:6px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                            <i data-lucide="download" style="width:14px;height:14px;"></i> Download Admission Letter (PDF)
                        </a>
                        <a href="{{ route('admissions.portal') }}" class="btn" style="background:#0A3E50;color:#ffffff;font-size:12px;font-weight:700;padding:8px 14px;border-radius:6px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                            <i data-lucide="check-square" style="width:14px;height:14px;"></i> Accept Offer & Confirm
                        </a>
                    </div>
                </div>
            </section>
        @endif

        <!-- Document Checklist -->
        <section class="panel">
            <div class="panel-head" style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h2>Required verification documents</h2>
                    <small style="color:var(--muted)">All documents must be clear PDF or image copies for Admissions Board verification</small>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Document Title</th>
                            <th>Requirement</th>
                            <th style="text-align:center;">Verification Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checklist as $item)
                            <tr>
                                <td>
                                    <strong style="color:#0A3E50;">{{ $item['label'] }}</strong>
                                    <div style="font-size:11px;color:#6b7280;">{{ $item['type'] }}</div>
                                </td>
                                <td>
                                    <span style="font-size:12px;color:#4b5563;">Mandatory</span>
                                </td>
                                <td style="text-align:center;">
                                    @if($item['status'] === 'VERIFIED')
                                        <span class="badge" style="background:#e6f4ea;color:#1E8449;font-weight:750;">
                                            <i data-lucide="check" style="width:12px;height:12px;margin-right:2px;vertical-align:middle;"></i> Verified
                                        </span>
                                    @elseif($item['status'] === 'REQUIRES_REUPLOAD')
                                        <span class="badge" style="background:#fef2f2;color:#dc2626;font-weight:750;">
                                            <i data-lucide="alert-triangle" style="width:12px;height:12px;margin-right:2px;vertical-align:middle;"></i> Re-upload Needed
                                        </span>
                                    @elseif($item['is_uploaded'])
                                        <span class="badge" style="background:#e6f1eb;color:#0A3E50;font-weight:750;">
                                            <i data-lucide="clock" style="width:12px;height:12px;margin-right:2px;vertical-align:middle;"></i> Under Review
                                        </span>
                                    @else
                                        <span class="badge" style="background:#f3f4f6;color:#6b7280;font-weight:700;">
                                            Pending Upload
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Right Column: Payment & Next Steps -->
    <div>
        <!-- Payment Card -->
        <section class="panel" style="margin-bottom:20px;">
            <div class="panel-head">
                <h2>Application processing fee</h2>
            </div>
            <div class="panel-body" style="padding:16px 20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span style="font-size:13px;color:#64748b;">Fee Amount</span>
                    <strong style="font-size:18px;color:#0A3E50;">KES {{ number_format($totalFee, 2) }}</strong>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;margin-bottom:14px;">
                    <span style="font-size:12px;color:#475569;">Payment Status:</span>
                    @if($isFeePaid)
                        <span class="badge" style="background:#e6f4ea;color:#1E8449;font-weight:800;">
                            PAID AND VERIFIED
                        </span>
                    @else
                        <span class="badge" style="background:#fef3c7;color:#92400e;font-weight:800;">
                            PENDING PAYMENT
                        </span>
                    @endif
                </div>

                @if($latestPayment)
                    <div style="font-size:11px;color:#64748b;margin-bottom:12px;">
                        <div><strong>Ref:</strong> {{ $latestPayment->reference ?? 'Pending Reference' }}</div>
                        <div><strong>Channel:</strong> {{ $latestPayment->channel ?? 'M-Pesa Express' }}</div>
                    </div>
                @endif

                @if(!$isFeePaid && $application)
                    <a href="{{ route('admissions.portal') }}" class="btn" style="width:100%;background:#E67E22;color:#ffffff;font-size:13px;font-weight:700;padding:10px;border-radius:8px;text-align:center;text-decoration:none;display:block;">
                        Pay KES {{ number_format($totalFee, 2) }} with M-Pesa
                    </a>
                @endif
            </div>
        </section>

        <!-- Next Steps Advice Card -->
        <section class="panel" style="background:#f8fafc;border:1px solid #e2e8f0;">
            <div class="panel-head">
                <h2>Admissions guidance</h2>
            </div>
            <div class="panel-body" style="padding:14px 16px;font-size:13px;color:#475569;line-height:1.5;">
                <p style="margin:0 0 10px;">
                    <strong>Need Help?</strong> Our Admissions Help Desk is available Monday to Friday, 8:00 AM – 5:00 PM EAT.
                </p>
                <div style="display:grid;gap:8px;margin-top:12px;">
                    <a href="{{ route('admissions.catalogue') }}" style="color:#0A3E50;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:6px;">
                        <i data-lucide="book-open" style="width:15px;height:15px;"></i> Browse All Programmes
                    </a>
                    <a href="{{ route('account.show', 'support') }}" style="color:#0A3E50;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:6px;">
                        <i data-lucide="help-circle" style="width:15px;height:15px;"></i> Submit Help Request
                    </a>
                </div>
            </div>
        </section>
    </div>
</div>
