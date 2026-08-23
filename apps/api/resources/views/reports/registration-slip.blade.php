<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#17343f;font-size:9px}h1{color:#0a3e50;font-size:16px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #cad6da;padding:4px}th{background:#0a3e50;color:#fff}
</style></head><body>
<h1>Mema University — Registration Confirmation Slip</h1>
<p><strong>{{ $registration->student?->person?->full_name }}</strong> · {{ $registration->student?->student_number }}</p>
<p>{{ $registration->term?->name }} ({{ $registration->term?->academicYear?->code }})</p>
<table><thead><tr><th>Code</th><th>Course</th><th>Credits</th></tr></thead><tbody>
@foreach($registration->courseEnrollments as $enrollment)
<tr>
    <td>{{ $enrollment->courseOffering?->course?->code }}</td>
    <td>{{ $enrollment->courseOffering?->course?->title }}</td>
    <td>{{ $enrollment->courseOffering?->course?->credits }}</td>
</tr>
@endforeach
</tbody></table>
<p>Registered on {{ $registration->registered_at?->toDayDateTimeString() }}</p>
</body></html>
