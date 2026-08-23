<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#17343f;font-size:10px}h1{color:#0a3e50}table{border-collapse:collapse;width:100%}th,td{border:1px solid #cad6da;padding:6px;text-align:left}th{background:#0a3e50;color:#fff}.muted{color:#60737a}
</style></head><body>
<h1>Mema University — Application Fee Revenue</h1>
<p class="muted">Generated {{ now()->toDayDateTimeString() }}</p>
<table><thead><tr><th>Receipt</th><th>Application</th><th>Channel</th><th>Amount</th><th>Paid At</th><th>Txn Ref</th></tr></thead><tbody>
@foreach($payments as $payment)
<tr>
    <td>{{ $payment->receipt_number }}</td>
    <td>{{ $payment->application?->application_number }}</td>
    <td>{{ $payment->channel }}</td>
    <td>{{ $payment->currency }} {{ $payment->amount }}</td>
    <td>{{ optional($payment->paid_at)?->toDayDateTimeString() }}</td>
    <td>{{ $payment->transaction_reference }}</td>
</tr>
@endforeach
</tbody></table></body></html>
