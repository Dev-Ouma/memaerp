<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#17343f;font-size:10px}h1{color:#0a3e50}table{border-collapse:collapse;width:100%}th,td{border:1px solid #cad6da;padding:6px;text-align:left}th{background:#0a3e50;color:#fff}.muted{color:#60737a}
</style></head><body>
<h1>Mema University — Master Course Catalogue</h1>
<p class="muted">Generated {{ now()->toDayDateTimeString() }}</p>
<table><thead><tr><th>Code</th><th>Title</th><th>Credits</th><th>Lecture</th><th>Lab</th><th>Department</th><th>Status</th></tr></thead><tbody>
@foreach($courses as $course)
<tr>
    <td>{{ $course->code }}</td>
    <td>{{ $course->title }}</td>
    <td>{{ $course->credits }}</td>
    <td>{{ $course->lecture_hours }}</td>
    <td>{{ $course->lab_hours }}</td>
    <td>{{ $course->department?->code }}</td>
    <td>{{ $course->status }}</td>
</tr>
@endforeach
</tbody></table></body></html>
