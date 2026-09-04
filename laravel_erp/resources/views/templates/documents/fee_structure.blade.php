<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>4-Year Degree Programme Fee Structure - {{ $payload['application']['programme_code'] }}</title>
<style>
    @page { size: A4 portrait; margin: 15mm; }
    body { font-family: Arial, sans-serif; color: #1e293b; line-height: 1.4; font-size: 8.5pt; }
    .header { text-align: center; border-bottom: 2px solid #0A3E50; padding-bottom: 6px; margin-bottom: 12px; }
    .header h1 { font-size: 14pt; color: #0A3E50; margin: 0; font-weight: 900; }
    .header h2 { font-size: 9.5pt; color: #1E8449; margin: 2px 0 0 0; font-weight: bold; }
    .title-box { background: #0A3E50; color: #ffffff; padding: 6px 10px; font-weight: bold; text-align: center; margin-bottom: 12px; border-radius: 4px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8pt; }
    th { background: #0A3E50; color: #ffffff; padding: 5px 8px; border: 1px solid #0A3E50; text-align: left; }
    td { padding: 5px 8px; border: 1px solid #cbd5e1; }
    .total-row { background: #f1f5f9; font-weight: bold; color: #0A3E50; border-top: 2px solid #0A3E50; }
</style>
</head>
<body>

<div class="header">
    <h1>MEMA UNIVERSITY COLLEGE</h1>
    <h2>FINANCE &amp; ACCOUNTS DIRECTORATE &bull; STUDENT BILLING</h2>
    <div style="font-size: 7.5pt; color: #64748b;">Finance Helpline: directorfinance@mema.ac.ke &bull; Tel: +254 20 491 3054</div>
</div>

<div class="title-box">
    APPROVED 4-YEAR FEES SCHEDULE: {{ strtoupper($payload['application']['programme_title']) }}
</div>

<p>
    <strong>Target Programme:</strong> {{ $payload['application']['programme_title'] }} ({{ $payload['application']['programme_code'] }}) &bull; 
    <strong>Duration:</strong> 4 Years (8 Semesters) &bull; <strong>Study Mode:</strong> Full Time Regular
</p>

<table>
    <thead>
        <tr>
            <th>Academic Year &amp; Semester</th>
            <th style="text-align: right;">Tuition Fee</th>
            <th style="text-align: right;">Statutory Levies</th>
            <th style="text-align: right;">Caution / ID</th>
            <th style="text-align: right;">Total Payable (KES)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Year 1 &bull; Semester 1</strong> (First Registration)</td>
            <td style="text-align: right;">48,500.00</td>
            <td style="text-align: right;">8,700.00</td>
            <td style="text-align: right;">6,000.00</td>
            <td style="text-align: right; font-weight: bold; color: #0A3E50;">63,200.00</td>
        </tr>
        <tr>
            <td><strong>Year 1 &bull; Semester 2</strong></td>
            <td style="text-align: right;">48,500.00</td>
            <td style="text-align: right;">6,700.00</td>
            <td style="text-align: right;">—</td>
            <td style="text-align: right; font-weight: bold;">55,200.00</td>
        </tr>
        <tr>
            <td><strong>Year 2 &bull; Semester 1</strong></td>
            <td style="text-align: right;">48,500.00</td>
            <td style="text-align: right;">6,700.00</td>
            <td style="text-align: right;">—</td>
            <td style="text-align: right; font-weight: bold;">55,200.00</td>
        </tr>
        <tr>
            <td><strong>Year 2 &bull; Semester 2</strong></td>
            <td style="text-align: right;">48,500.00</td>
            <td style="text-align: right;">6,700.00</td>
            <td style="text-align: right;">—</td>
            <td style="text-align: right; font-weight: bold;">55,200.00</td>
        </tr>
        <tr>
            <td><strong>Year 3 &bull; Semester 1</strong> (Industrial Attachment)</td>
            <td style="text-align: right;">48,500.00</td>
            <td style="text-align: right;">8,700.00</td>
            <td style="text-align: right;">—</td>
            <td style="text-align: right; font-weight: bold;">57,200.00</td>
        </tr>
        <tr>
            <td><strong>Year 3 &bull; Semester 2</strong></td>
            <td style="text-align: right;">48,500.00</td>
            <td style="text-align: right;">6,700.00</td>
            <td style="text-align: right;">—</td>
            <td style="text-align: right; font-weight: bold;">55,200.00</td>
        </tr>
        <tr>
            <td><strong>Year 4 &bull; Semester 1</strong></td>
            <td style="text-align: right;">48,500.00</td>
            <td style="text-align: right;">6,700.00</td>
            <td style="text-align: right;">—</td>
            <td style="text-align: right; font-weight: bold;">55,200.00</td>
        </tr>
        <tr>
            <td><strong>Year 4 &bull; Semester 2</strong> (Graduation Fee)</td>
            <td style="text-align: right;">48,500.00</td>
            <td style="text-align: right;">11,700.00</td>
            <td style="text-align: right;">—</td>
            <td style="text-align: right; font-weight: bold;">60,200.00</td>
        </tr>
        <tr class="total-row">
            <td><strong>CUMULATIVE 4-YEAR PROGRAMME COST</strong></td>
            <td style="text-align: right;">388,000.00</td>
            <td style="text-align: right;">62,600.00</td>
            <td style="text-align: right;">6,000.00</td>
            <td style="text-align: right; font-size: 9pt;">KES 456,600.00</td>
        </tr>
    </tbody>
</table>

<div style="background: #f8fafc; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 8pt;">
    <strong>OFFICIAL PAYMENT CHANNELS:</strong><br>
    &bull; <strong>M-Pesa Paybill:</strong> 222111 &bull; <strong>Account:</strong> [Student Admission Number]<br>
    &bull; <strong>KCB Bank:</strong> A/C No: 1109283741 &bull; <strong>Branch:</strong> University Way Nairobi<br>
    &bull; <strong>HELB / HEF / Sponsorships:</strong> Invoices generated directly to Higher Education Financing Portal.
</div>

</body>
</html>
