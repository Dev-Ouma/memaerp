<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Letter - {{ $application->application_number }} - MEMA College</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif, system-ui;
            color: #1e293b;
            line-height: 1.5;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
        }
        .letter-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px 50px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0A3E50;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logo-mark {
            width: 60px;
            height: 60px;
            background: #0A3E50;
            color: #ffffff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            font-family: sans-serif;
            border: 2px solid #E67E22;
        }
        .college-info h1 {
            font-size: 20px;
            color: #0A3E50;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 800;
        }
        .college-info p {
            margin: 2px 0 0;
            font-size: 11px;
            color: #64748b;
            font-family: sans-serif;
        }
        .letter-ref-box {
            text-align: right;
            font-size: 11px;
            font-family: sans-serif;
        }
        .letter-ref-box strong {
            color: #0A3E50;
        }
        .recipient-box {
            margin-bottom: 20px;
            font-size: 13px;
        }
        .letter-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            color: #0A3E50;
            margin: 25px 0 15px;
            text-transform: uppercase;
        }
        .content {
            font-size: 13px;
            text-align: justify;
            margin-bottom: 25px;
        }
        .content p {
            margin-bottom: 12px;
        }
        .offer-details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 12px;
        }
        .offer-details-table th, .offer-details-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            text-align: left;
        }
        .offer-details-table th {
            background: #f1f5f9;
            color: #0A3E50;
            width: 35%;
        }
        .signoff-area {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 35px;
            padding-top: 15px;
        }
        .signature-box {
            font-size: 12px;
        }
        .signature-line {
            width: 200px;
            border-top: 1px solid #0A3E50;
            margin-top: 40px;
            padding-top: 4px;
            font-weight: bold;
        }
        .qr-box {
            text-align: center;
            border: 1px dashed #cbd5e1;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 10px;
            font-family: monospace;
            background: #f8fafc;
        }
        .qr-code-placeholder {
            width: 70px;
            height: 70px;
            margin: 0 auto 5px;
            background: #0A3E50;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            border-radius: 4px;
        }
        .watermark {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 80px;
            color: rgba(10, 62, 80, 0.04);
            font-weight: 900;
            pointer-events: none;
            text-transform: uppercase;
        }
        .print-toolbar {
            max-width: 800px;
            margin: 0 auto 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .print-btn {
            background: #0A3E50;
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .print-btn:hover {
            background: #08303e;
        }
        .back-btn {
            color: #64748b;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }
        .back-btn:hover {
            color: #0A3E50;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .print-toolbar {
                display: none !important;
            }
            .letter-container {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="print-toolbar">
        <a href="javascript:history.back()" class="back-btn">&larr; Back to Portal</a>
        <button onclick="window.print()" class="print-btn">
            Print / Save as PDF
        </button>
    </div>

    <div class="letter-container">
        <div class="watermark">MEMA COLLEGE</div>

        <div class="header">
            <div class="logo-area">
                <div class="logo-mark">MC</div>
                <div class="college-info">
                    <h1>MEMA COLLEGE</h1>
                    <p>Office of the Academic Registrar · Admissions Office</p>
                    <p>P.O. Box 90120-00100 Nairobi, Kenya · Tel: +254 700 000000</p>
                    <p>Email: admissions@mema.ac.ke · Web: www.mema.ac.ke</p>
                </div>
            </div>
            <div class="letter-ref-box">
                <div><strong>Ref:</strong> {{ $offer?->offer_number ?? 'MC/ADM/2026/'.str_pad((string)$application->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div><strong>App No:</strong> {{ $application->application_number }}</div>
                <div><strong>Date:</strong> {{ $offer?->issued_at ? date('d F, Y', strtotime((string)$offer->issued_at)) : now()->format('d F, Y') }}</div>
            </div>
        </div>

        <div class="recipient-box">
            <div><strong>To:</strong> {{ $application->applicant->user->name }}</div>
            <div><strong>Email:</strong> {{ $application->applicant->user->email }}</div>
            <div><strong>Phone:</strong> {{ $application->applicant->phone ?? '—' }}</div>
            <div><strong>Nationality:</strong> {{ $application->applicant->nationality ?? 'Kenyan' }}</div>
        </div>

        <div class="letter-title">
            Letter of Provisional Admission - 2026/2027 Academic Year
        </div>

        <div class="content">
            <p>Dear {{ $application->applicant->user->first_name ?: $application->applicant->user->name }},</p>

            <p>
                Following your application and the subsequent review by the College Admissions Board and Academic Senate, I am pleased to inform you that you have been offered provisional admission to <strong>MEMA College</strong> to pursue the following programme of study:
            </p>

            <table class="offer-details-table">
                <tr>
                    <th>Programme of Study</th>
                    <td><strong>{{ $application->offering->course->name }}</strong> (Code: {{ $application->offering->course->code }})</td>
                </tr>
                <tr>
                    <th>School / Faculty</th>
                    <td>{{ 'MEMA University College' }}</td>
                </tr>
                <tr>
                    <th>Intake &amp; Academic Session</th>
                    <td>{{ $application->offering->intake->name }} (2026/2027 Academic Year)</td>
                </tr>
                <tr>
                    <th>Campus &amp; Mode of Study</th>
                    <td>{{ $application->offering->campus ?? 'Main Campus' }} · {{ $application->offering->study_mode ?? 'Full-time' }}</td>
                </tr>
                <tr>
                    <th>Reporting Date &amp; Orientation</th>
                    <td><strong>Monday, 14th September 2026 at 08:30 AM EAT</strong></td>
                </tr>
                <tr>
                    <th>Offer Acceptance Deadline</th>
                    <td><strong>{{ $offer?->expires_at ? date('d F, Y', strtotime((string)$offer->expires_at)) : '30 September 2026' }}</strong></td>
                </tr>
            </table>

            <p>
                This offer of admission is subject to the following statutory terms and conditions:
            </p>
            <ol style="padding-left: 20px; font-size: 12px; margin-bottom: 15px;">
                <li>Verification and presentation of original academic certificates, result slips, and national identification document during reporting.</li>
                <li>Payment of required tuition fees and statutory charges as outlined in the college fees schedule.</li>
                <li>Compliance with all College Rules and Regulations, Student Code of Conduct, and examination policies.</li>
                <li>Formal acceptance of this offer through your online applicant portal before the stated acceptance deadline.</li>
            </ol>

            <p>
                On behalf of the College Council, Management Board, and Faculty, congratulations on your admission. We look forward to welcoming you to MEMA College.
            </p>
        </div>

        <div class="signoff-area">
            <div class="signature-box">
                <div>Yours faithfully,</div>
                <div class="signature-line">
                    <div>DR. JULIUS K. MWANGI</div>
                    <div style="font-size: 11px; color: #64748b; font-weight: normal;">Academic Registrar &amp; Secretary to Senate</div>
                </div>
            </div>

            <div class="qr-box">
                <div class="qr-code-placeholder">QR TOKEN</div>
                <div><strong>Token:</strong> {{ substr($offer?->verification_token ?? hash('sha256', (string)$application->id), 0, 16) }}...</div>
                <div style="color: #64748b; font-size: 9px; margin-top: 2px;">Verify at: www.mema.ac.ke/verify</div>
            </div>
        </div>
    </div>
</body>
</html>
