<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#17343f;font-size:9px}h1{color:#0a3e50;font-size:16px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #cad6da;padding:4px;text-align:left}th{background:#0a3e50;color:#fff}.muted{color:#60737a}
</style></head><body>
<h1>Mema University — Official Matriculation Register</h1>
<p class="muted">Generated {{ now()->toDayDateTimeString() }}</p>
<table><thead><tr><th>Student No</th><th>Full Name</th><th>Programme</th><th>Intake</th><th>Matriculated</th><th>Officer</th><th>Pledge</th></tr></thead><tbody>
@foreach($logs as $log)
<tr>
    <td>{{ $log->student?->student_number }}</td>
    <td>{{ $log->student?->person?->full_name }}</td>
    <td>{{ $log->student?->programme?->code }}</td>
    <td>{{ $log->student?->intake?->code }}</td>
    <td>{{ $log->matriculated_at?->toDayDateTimeString() }}</td>
    <td>{{ $log->matriculatedBy?->email }}</td>
    <td>{{ $log->pledge_signed ? 'Yes' : 'No' }}</td>
</tr>
@endforeach
</tbody></table></body></html>
