<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#17343f;font-size:9px}h1{color:#0a3e50;font-size:16px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #cad6da;padding:4px;text-align:left}th{background:#0a3e50;color:#fff}.muted{color:#60737a}
</style></head><body>
<h1>Mema University — Admissions Intake Roll</h1>
<p class="muted">Generated {{ now()->toDayDateTimeString() }}</p>
<table><thead><tr><th>App No</th><th>Applicant</th><th>Programme</th><th>Campus</th><th>Intake</th><th>Grade</th><th>Score</th><th>Fee</th><th>Status</th></tr></thead><tbody>
@foreach($applications as $application)
<tr>
    <td>{{ $application->application_number }}</td>
    <td>{{ $application->person?->full_name }}</td>
    <td>{{ $application->programme?->code }}</td>
    <td>{{ $application->campus?->code }}</td>
    <td>{{ $application->intake?->code }}</td>
    <td>{{ $application->mean_grade }}</td>
    <td>{{ $application->qualification_score }}</td>
    <td>{{ $application->is_fee_paid ? 'Paid' : 'Unpaid' }}</td>
    <td>{{ $application->status }}</td>
</tr>
@endforeach
</tbody></table></body></html>
