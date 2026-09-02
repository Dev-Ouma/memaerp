<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }} - MEMA ERP Official Report</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 15mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Quicksand', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #1E293B;
            background-color: #FFFFFF;
            font-size: 11px;
            line-height: 1.4;
            padding: 24px;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2.5px solid #0A3E50;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .brand-block {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo-badge {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #0A3E50 0%, #007A8C 100%);
            color: #FFFFFF;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
            letter-spacing: -0.5px;
            box-shadow: 0 4px 10px rgba(10, 62, 80, 0.2);
        }

        .brand-info h1 {
            font-size: 18px;
            font-weight: 800;
            color: #0A3E50;
            letter-spacing: -0.3px;
            text-transform: uppercase;
        }

        .brand-info p {
            font-size: 11px;
            font-weight: 600;
            color: #64748B;
            margin-top: 1px;
        }

        .meta-block {
            text-align: right;
            font-size: 10.5px;
            color: #475569;
        }

        .meta-block .report-badge {
            display: inline-block;
            background: #E67E22;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .report-summary-bar {
            display: flex;
            gap: 14px;
            margin-bottom: 16px;
        }

        .summary-card {
            flex: 1;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-left: 4px solid #0A3E50;
            border-radius: 6px;
            padding: 8px 12px;
        }

        .summary-card.accent {
            border-left-color: #E67E22;
        }

        .summary-card.success {
            border-left-color: #10B981;
        }

        .summary-card .label {
            font-size: 10px;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
        }

        .summary-card .value {
            font-size: 15px;
            font-weight: 800;
            color: #0A3E50;
            margin-top: 2px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 8px;
            page-break-inside: auto;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .data-table th {
            background-color: #0A3E50 !important;
            color: #FFFFFF !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9.5px;
            letter-spacing: 0.3px;
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #0A3E50;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .data-table td {
            padding: 6.5px 10px;
            border: 1px solid #E2E8F0;
            color: #1E293B;
            font-weight: 500;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #F8FAFC !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .data-table td.font-mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9.5px;
            font-weight: 600;
        }

        .status-pill {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-active, .status-enrolled, .status-admitted, .status-paid {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .status-pending, .status-in-progress, .status-review {
            background: #FFFBEB;
            color: #92400E;
            border: 1px solid #FDE68A;
        }

        .status-rejected, .status-declined {
            background: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        .footer-container {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px dashed #CBD5E1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9.5px;
            color: #64748B;
        }

        .no-print-toolbar {
            position: fixed;
            top: 16px;
            right: 16px;
            background: #0A3E50;
            color: #FFFFFF;
            padding: 8px 16px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
        }

        .no-print-toolbar button {
            background: #E67E22;
            color: #FFFFFF;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-family: 'Quicksand', sans-serif;
            font-weight: 700;
            font-size: 11px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .no-print-toolbar button:hover {
            background: #D35400;
        }

        @media print {
            .no-print-toolbar {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-toolbar">
        <span>Print Preview / PDF Generator</span>
        <button onclick="window.print()">Print / Save as PDF</button>
        <button onclick="window.close()" style="background: rgba(255,255,255,0.2);">Close</button>
    </div>

    {{-- Official Institutional Header --}}
    <div class="header-container">
        <div class="brand-block">
            <div class="brand-logo-badge">MEMA</div>
            <div class="brand-info">
                <h1>MEMA UNIVERSITY COLLEGE</h1>
                <p>Enterprise Resource Planning & Academic Management System</p>
                <p style="color: #0A3E50; font-weight: 700; margin-top: 2px;">{{ $reportTitle }}</p>
            </div>
        </div>
        <div class="meta-block">
            <div class="report-badge">Official Record</div>
            <div><strong>Generated At:</strong> {{ now()->format('d-M-Y H:i:s') }} EAT</div>
            <div><strong>Generated By:</strong> {{ auth()->user()->name ?? 'System Administrator' }}</div>
            <div><strong>Audit Hash:</strong> <span style="font-family: 'JetBrains Mono', monospace; font-size: 9px;">{{ substr(sha1($reportTitle.now()->toDateString()), 0, 16) }}</span></div>
        </div>
    </div>

    {{-- Summary Cards --}}
    @if(!empty($summaryStats))
        <div class="report-summary-bar">
            @foreach($summaryStats as $label => $val)
                <div class="summary-card {{ $loop->iteration == 2 ? 'accent' : ($loop->iteration == 3 ? 'success' : '') }}">
                    <div class="label">{{ $label }}</div>
                    <div class="value">{{ $val }}</div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Data Table --}}
    <table class="data-table">
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $key => $cell)
                        @php
                            $str = is_null($cell) ? '—' : (is_bool($cell) ? ($cell ? 'Yes' : 'No') : (is_array($cell) ? implode(', ', $cell) : (string)$cell));
                            $lower = strtolower($str);
                            $isStatus = in_array($lower, ['active', 'inactive', 'enrolled', 'admitted', 'paid', 'pending', 'in progress', 'rejected', 'declined', 'draft']);
                        @endphp
                        <td class="{{ preg_match('/^(SCH-|APP-|STU-|REG-|REF-|KES|EMP-|\+254|\d{4,})/', $str) ? 'font-mono' : '' }}">
                            @if($isStatus)
                                <span class="status-pill status-{{ str_replace(' ', '-', $lower) }}">{{ $str }}</span>
                            @else
                                {{ $str }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" style="text-align: center; padding: 24px; color: #94A3B8;">
                        No records matching the selected parameters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer-container">
        <div>MEMA University College ERP • Institutional Intelligence & Audit Unit</div>
        <div>Page 1 of 1 • System Color Theme: Dark Teal (#0A3E50) & Accent Orange (#E67E22)</div>
        <div>CONFIDENTIAL & RESTRICTED</div>
    </div>

</body>
</html>
