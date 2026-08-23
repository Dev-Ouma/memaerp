<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#17343f;font-size:10px}h1{color:#0a3e50}table{border-collapse:collapse;width:100%}th,td{border:1px solid #cad6da;padding:6px;text-align:left}th{background:#0a3e50;color:#fff}.muted{color:#60737a}
</style></head><body>
<h1>Mema University — Organizational Master Directory</h1><p class="muted">Generated {{ now()->toDayDateTimeString() }}</p>
<table><thead><tr><th>Campus</th><th>Faculty / School</th><th>Department</th><th>Status</th></tr></thead><tbody>
@foreach($campuses as $campus) @foreach($campus->faculties as $faculty) @foreach($faculty->departments as $department)
<tr><td>{{ $campus->code }} — {{ $campus->name }}</td><td>{{ $faculty->code }} — {{ $faculty->name }}</td><td>{{ $department->code }} — {{ $department->name }}</td><td>{{ $department->status }}</td></tr>
@endforeach @endforeach @endforeach
</tbody></table></body></html>
