<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#17343f;font-size:11px}h1,h2{color:#0a3e50}.term{border-left:4px solid #138a72;margin:12px 0;padding:8px 12px;background:#f1f8f6}table{border-collapse:collapse;width:100%}th,td{border:1px solid #cad6da;padding:6px;text-align:left}th{background:#0a3e50;color:#fff}.muted{color:#60737a}
</style></head><body>
<h1>Mema University Academic Calendar</h1><h2>{{ $year->name }}</h2><p class="muted">Senate resolution: {{ $year->senate_resolution_reference ?? 'Draft' }}</p>
@foreach($year->terms as $term)<div class="term"><strong>{{ $term->name }} · {{ $term->study_mode_code }}</strong><br>{{ $term->starts_on->toFormattedDateString() }} – {{ $term->ends_on->toFormattedDateString() }}<br>Registration: {{ optional($term->registration_opens_at)->toDayDateTimeString() ?? 'Not set' }} – {{ optional($term->registration_closes_at)->toDayDateTimeString() ?? 'Not set' }}</div>@endforeach
<h2>Events and deadlines</h2><table><thead><tr><th>Date</th><th>Event</th><th>Type</th></tr></thead><tbody>@forelse($events as $event)<tr><td>{{ $event->starts_at->toDayDateTimeString() }}</td><td>{{ $event->title }}</td><td>{{ $event->event_type }}</td></tr>@empty<tr><td colspan="3">No additional events published.</td></tr>@endforelse</tbody></table>
</body></html>
