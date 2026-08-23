<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#17343f}h1{color:#0a3e50;font-size:16px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #cad6da;padding:3px}th{background:#0a3e50;color:#fff}
</style></head><body>
<h1>Mema University — Official Academic Transcript</h1>
<p><strong>{{ $student->person?->full_name }}</strong> · {{ $student->student_number }}</p>
<p>{{ $student->programme?->name }}</p>
@foreach($student->termRegistrations as $registration)
<h3>{{ $registration->term?->name }}</h3>
<table><thead><tr><th>Code</th><th>Course</th><th>Total</th><th>Grade</th></tr></thead><tbody>
@foreach($registration->courseEnrollments as $enrollment)
<tr>
<td>{{ $enrollment->courseOffering?->course?->code }}</td>
<td>{{ $enrollment->courseOffering?->course?->title }}</td>
<td>{{ $enrollment->mark?->total_score }}</td>
<td>{{ $enrollment->mark?->letter_grade }}</td>
</tr>
@endforeach
</tbody></table>
@endforeach
</body></html>
