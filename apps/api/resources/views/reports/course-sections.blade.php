<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#17343f;font-size:10px}h1{color:#0a3e50}table{border-collapse:collapse;width:100%}th,td{border:1px solid #cad6da;padding:6px;text-align:left}th{background:#0a3e50;color:#fff}.muted{color:#60737a}
</style></head><body>
<h1>Mema University — Semester Class Section Allocation</h1>
<p class="muted">Generated {{ now()->toDayDateTimeString() }}</p>
<table><thead><tr><th>Course</th><th>Section</th><th>Campus</th><th>Term</th><th>Lecturer</th><th>Enrolled</th><th>Capacity</th><th>Status</th></tr></thead><tbody>
@foreach($offerings as $offering)
<tr>
    <td>{{ $offering->course?->code }} — {{ $offering->course?->title }}</td>
    <td>{{ $offering->section_code }}</td>
    <td>{{ $offering->campus?->code }}</td>
    <td>{{ $offering->term?->code }}</td>
    <td>{{ $offering->lecturer?->email }}</td>
    <td>{{ $offering->enrolled_count }}</td>
    <td>{{ $offering->max_capacity }}</td>
    <td>{{ $offering->status }}</td>
</tr>
@endforeach
</tbody></table></body></html>
