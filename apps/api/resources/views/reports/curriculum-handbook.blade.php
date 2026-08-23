<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#17343f;font-size:10px}h1,h2{color:#0a3e50}table{border-collapse:collapse;width:100%;margin-bottom:18px}th,td{border:1px solid #cad6da;padding:6px;text-align:left}th{background:#0a3e50;color:#fff}.badge{padding:3px 7px;background:#e8f4f1}.muted{color:#60737a}
</style></head><body>
<h1>{{ $version->programme->name }}</h1><p><strong>{{ $version->programme->code }}</strong> · {{ $version->programme->award_level }} · Curriculum {{ $version->version_code }}</p>
<p class="muted">Department: {{ $version->programme->department->name }} · Effective {{ $version->effectiveYear->name }} · Status {{ $version->status }}</p>
<p>Graduation credits: <strong>{{ $version->graduation_credits_required }}</strong> · Senate resolution: {{ $version->senate_approval_ref ?? 'Pending' }}</p>
@foreach($version->curriculumCourses->groupBy('year_level') as $year => $yearCourses)
<h2>Year {{ $year }}</h2><table><thead><tr><th>Semester</th><th>Course</th><th>Title</th><th>Credits</th><th>Type</th></tr></thead><tbody>
@foreach($yearCourses->sortBy('semester') as $item)<tr><td>{{ $item->semester }}</td><td>{{ $item->course->code }}</td><td>{{ $item->course->title }}</td><td>{{ $item->course->credits }}</td><td>{{ $item->course_type }} @if($item->electiveGroup) ({{ $item->electiveGroup->code }}) @endif</td></tr>@endforeach
</tbody></table>@endforeach
<h2>Approval chain</h2><p>@foreach($version->reviewSteps->sortBy('sequence') as $step)<span class="badge">{{ $step->stage }}: {{ $step->status }}</span> @endforeach</p>
<p class="muted">Structure hash: {{ $version->structure_hash ?? 'Generated on Senate approval' }}</p>
</body></html>
