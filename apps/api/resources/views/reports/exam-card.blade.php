<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#17343f;font-size:10px}h1{color:#0a3e50;font-size:16px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #cad6da;padding:4px}th{background:#0a3e50;color:#fff}
</style></head><body>
<h1>Mema University — Examination Card</h1>
<p><strong>{{ $student->person?->full_name }}</strong> · {{ $student->student_number }}</p>
<p>{{ $term->name }} · {{ $student->programme?->name }}</p>
<table><thead><tr><th>Code</th><th>Course</th></tr></thead><tbody>
@foreach($courses as $enrollment)
<tr><td>{{ $enrollment->courseOffering?->course?->code }}</td><td>{{ $enrollment->courseOffering?->course?->title }}</td></tr>
@endforeach
</tbody></table>
<p>Verify: {{ $verifyUrl }}</p>
</body></html>
