<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#17343f;font-size:8px;margin:0;padding:12px}
.card{border:2px solid #0a3e50;border-radius:8px;padding:10px;height:100%}
.header{background:#0a3e50;color:#fff;padding:6px 8px;border-radius:4px;margin-bottom:8px}
h1{font-size:11px;margin:0}h2{font-size:9px;margin:4px 0 0;color:#60737a}
.row{display:table;width:100%}.col{display:table-cell;vertical-align:top}
.photo{width:48px;height:60px;border:1px solid #cad6da;background:#eef4f6;text-align:center;line-height:60px;color:#60737a}
.label{color:#60737a;font-size:7px}.value{font-size:9px;font-weight:bold}
.qr{margin-top:6px;text-align:center;font-size:6px;color:#60737a;word-break:break-all}
</style></head><body>
<div class="card">
<div class="header"><h1>Mema University — Digital Student ID</h1><h2>Official verification card</h2></div>
<div class="row">
<div class="col" style="width:56px"><div class="photo">PHOTO</div></div>
<div class="col" style="padding-left:8px">
<div><span class="label">Student Number</span><br><span class="value">{{ $student->student_number }}</span></div>
<div style="margin-top:4px"><span class="label">Full Name</span><br><span class="value">{{ $student->person?->full_name }}</span></div>
<div style="margin-top:4px"><span class="label">Programme</span><br><span class="value">{{ $student->programme?->code }} — {{ $student->programme?->name }}</span></div>
<div style="margin-top:4px"><span class="label">Campus</span><br><span class="value">{{ $student->campus?->name }}</span></div>
<div style="margin-top:4px"><span class="label">Valid Until Review</span><br><span class="value">{{ now()->addYear()->toFormattedDateString() }}</span></div>
</div>
</div>
<div class="qr">Scan to verify: {{ $verifyUrl }}</div>
</div></body></html>
