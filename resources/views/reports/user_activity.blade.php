<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1e293b;
            background: #fff;
        }

        .page-header {
            background: linear-gradient(135deg, #581c87 0%, #7c3aed 60%, #a78bfa 100%);
            padding: 28px 32px 22px;
        }

        .page-header h1 {
            font-size: 22px;
            color: #fff;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .page-header .subtitle {
            color: #ddd6fe;
            font-size: 11px;
            margin-top: 4px;
        }

        .page-header .meta {
            margin-top: 14px;
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 6px;
            padding: 6px 14px;
            color: #fff;
            font-size: 10px;
        }

        .meta span {
            opacity: 0.75;
            margin-right: 4px;
        }

        .stats-bar {
            background: #faf5ff;
            border-bottom: 1px solid #ede9fe;
            padding: 14px 32px;
            display: table;
            width: 100%;
        }

        .stat-item {
            display: table-cell;
            text-align: center;
        }

        .stat-item .val {
            font-size: 20px;
            font-weight: 700;
            color: #581c87;
        }

        .stat-item .lbl {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .stat-divider {
            display: table-cell;
            width: 1px;
            background: #ede9fe;
        }

        .content {
            padding: 24px 32px 32px;
        }

        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #7c3aed;
            display: inline-block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        thead tr {
            background: #581c87;
        }

        thead th {
            padding: 9px 10px;
            color: #fff;
            font-weight: 600;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            text-align: left;
        }

        tbody tr:nth-child(even) td {
            background: #faf5ff;
        }

        tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #ede9fe;
            color: #334155;
            font-size: 9.5px;
            vertical-align: middle;
        }

        .avatar {
            display: inline-block;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            text-align: center;
            line-height: 22px;
            margin-right: 6px;
            vertical-align: middle;
        }

        .bar-container {
            background: #ede9fe;
            border-radius: 20px;
            height: 8px;
            width: 100%;
            min-width: 80px;
        }

        .bar-fill {
            background: linear-gradient(90deg, #7c3aed, #a78bfa);
            border-radius: 20px;
            height: 8px;
        }

        .activity-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 4px;
            vertical-align: middle;
        }

        .dot-high {
            background: #16a34a;
        }

        .dot-medium {
            background: #f59e0b;
        }

        .dot-low {
            background: #e2e8f0;
        }

        .footer {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #ede9fe;
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            color: #94a3b8;
            font-size: 8px;
        }

        .footer-right {
            display: table-cell;
            text-align: right;
            color: #94a3b8;
            font-size: 8px;
        }

        .footer-brand {
            color: #7c3aed;
            font-weight: 700;
        }
    </style>
</head>

<body>

    <div class="page-header">
        <h1>User Activity Report</h1>
        <div class="subtitle">Reservation activity per user in the selected period</div>
        <div class="meta">
            <span>Period:</span> {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
        </div>
    </div>

    @php
    $total = $rows->sum('reservations_count');
    $maxVal = $rows->max('reservations_count') ?: 1;
    $active = $rows->where('reservations_count', '>', 0)->count();
    $inactive = $rows->where('reservations_count', 0)->count();
    @endphp

    <div class="stats-bar">
        <div class="stat-item">
            <div class="val">{{ $rows->count() }}</div>
            <div class="lbl">Total Users</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="val">{{ $active }}</div>
            <div class="lbl">Active Users</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="val">{{ $total }}</div>
            <div class="lbl">Total Reservations</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="val">{{ $inactive }}</div>
            <div class="lbl">Inactive Users</div>
        </div>
    </div>

    <div class="content">
        <div class="section-title">User Activity Details</div>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th style="width:110px">Reservations</th>
                    <th style="width:160px">Activity Level</th>
                    <th style="width:55px">Share</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows->sortByDesc('reservations_count')->values() as $u)
                @php
                $initials = strtoupper(substr($u->name ?? 'U', 0, 1) . substr($u->last_name ?? '', 0, 1));
                $pct = $total > 0 ? round($u->reservations_count / $total * 100, 1) : 0;
                $barWidth = round($u->reservations_count / $maxVal * 100);
                $dotClass = $u->reservations_count >= 5 ? 'dot-high' : ($u->reservations_count > 0 ? 'dot-medium' : 'dot-low');
                @endphp
                <tr>
                    <td>
                        <span class="avatar">{{ $initials }}</span>
                        <strong>{{ $u->name }} {{ $u->last_name }}</strong>
                    </td>
                    <td style="color:#64748b">{{ $u->email }}</td>
                    <td style="text-align:center"><strong>{{ $u->reservations_count }}</strong></td>
                    <td>
                        <span class="activity-dot {{ $dotClass }}"></span>
                        <div class="bar-container" style="display:inline-block;width:calc(100% - 16px);vertical-align:middle;">
                            <div class="bar-fill" style="width:{{ $barWidth }}%"></div>
                        </div>
                    </td>
                    <td style="text-align:center">{{ $pct }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:20px;color:#94a3b8;">No records found for this period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <div class="footer-left">Generated on {{ now()->format('F d, Y \a\t H:i') }}</div>
            <div class="footer-right"><span class="footer-brand">LabSystem</span> &bull; Confidential</div>
        </div>
    </div>

</body>

</html>