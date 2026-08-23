<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#17343f;font-size:11px;line-height:1.45}
.header{border-bottom:3px solid #0a3e50;padding-bottom:12px;margin-bottom:24px}
h1{color:#0a3e50;margin:0;font-size:20px}h2{color:#1e8449;margin:0 0 8px;font-size:14px}
.muted{color:#60737a}.box{border:1px solid #cad6da;padding:14px;margin:18px 0}
.qr{float:right;width:90px;height:90px;border:2px solid #0a3e50;text-align:center;font-size:8px;padding-top:28px}
.footer{margin-top:40px;border-top:1px solid #cad6da;padding-top:10px;font-size:9px;color:#60737a}
</style></head><body>
<div class="header">
    <div class="qr">QR<br>{{ substr((string) $application->offer_qr_token, 0, 12) }}</div>
    <h1>{{ $application->institution?->name ?? 'Mema University' }}</h1>
    <p class="muted">Office of Admissions · Official Offer Letter</p>
</div>
<h2>Admission Offer — {{ $application->offer_letter_ref }}</h2>
<p>Dear {{ $application->person?->full_name }},</p>
<p>Congratulations. You have been offered admission to:</p>
<div class="box">
    <strong>{{ $application->programme?->name }}</strong> ({{ $application->programme?->code }})<br>
    Campus: {{ $application->campus?->name }}<br>
    Intake: {{ $application->intake?->name ?? $application->academicYear?->code }}<br>
    Application: {{ $application->application_number }}
</div>
<p>This offer expires on <strong>{{ optional($application->offer_expires_at)?->toFormattedDateString() ?? 'N/A' }}</strong>. Accept online via the applicant portal to proceed to matriculation.</p>
<p>Verification hash: <span class="muted">{{ $application->offer_letter_hash }}</span></p>
<div class="footer">Tamper-evident offer · Generated {{ now()->toDayDateTimeString() }} · Document integrity token bound to application id.</div>
</body></html>
