<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#17343f;font-size:11px}h1,h2{color:#0a3e50}.muted{color:#60737a}table{border-collapse:collapse;width:100%}th,td{border:1px solid #cad6da;padding:6px;text-align:left}
</style></head><body>
<h1>{{ $course->code }} — {{ $course->title }}</h1>
<p class="muted">{{ $course->department?->faculty?->name }} · {{ $course->department?->name }} · {{ $course->credits }} credits</p>
<h2>Contact hours</h2>
<p>Lecture {{ $course->lecture_hours }} · Laboratory {{ $course->lab_hours }} · Tutorial {{ $course->tutorial_hours }}</p>
<h2>Learning outcomes</h2>
<p>{{ $course->learning_outcomes ?: 'Not yet recorded.' }}</p>
<h2>Syllabus outline</h2>
<p>{{ $course->syllabus_outline ?: $course->description ?: 'Not yet recorded.' }}</p>
<h2>Prerequisites</h2>
@if($course->prerequisites->isEmpty())
<p class="muted">None recorded.</p>
@else
<table><thead><tr><th>Type</th><th>Required course</th></tr></thead><tbody>
@foreach($course->prerequisites as $requirement)
<tr><td>{{ $requirement->requirement_type }}</td><td>{{ $requirement->prerequisiteCourse?->code }} — {{ $requirement->prerequisiteCourse?->title }}</td></tr>
@endforeach
</tbody></table>
@endif
</body></html>
