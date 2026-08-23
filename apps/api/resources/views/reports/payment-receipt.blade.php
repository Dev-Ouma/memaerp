<!doctype html>
<html lang="en"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#17343f;font-size:10px}h1{color:#0a3e50;font-size:16px}.row{margin-bottom:6px}.label{color:#60737a}
</style></head><body>
<h1>Mema University — Official Payment Receipt</h1>
<p class="row"><span class="label">Receipt No:</span> {{ $payment->receipt_number }}</p>
<p class="row"><span class="label">Payer:</span> {{ $payment->person?->full_name }}</p>
<p class="row"><span class="label">Invoice:</span> {{ $payment->invoice?->invoice_number }}</p>
<p class="row"><span class="label">Amount:</span> KES {{ number_format((float) $payment->amount, 2) }}</p>
<p class="row"><span class="label">Method:</span> {{ $payment->payment_method }}</p>
<p class="row"><span class="label">Reference:</span> {{ $payment->transaction_reference }}</p>
<p class="row"><span class="label">Paid at:</span> {{ $payment->paid_at?->toDayDateTimeString() }}</p>
</body></html>
