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
            background: linear-gradient(135deg, #065f46 0%, #059669 60%, #34d399 100%);
            padding: 28px 32px 22px;
        }

        .page-header h1 {
            font-size: 22px;
            color: #fff;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .page-header .subtitle {
            color: #a7f3d0;
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
            background: #f0fdf4;
            border-bottom: 1px solid #d1fae5;
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
            color: #065f46;
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
            background: #d1fae5;
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
            border-bottom: 2px solid #059669;
            display: inline-block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        thead tr {
            background: #065f46;
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
            background: #f0fdf4;
        }

        tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #d1fae5;
            color: #334155;
            font-size: 9.5px;
            vertical-align: middle;
        }

        .bar-container {
            background: #d1fae5;
            border-radius: 20px;
            height: 8px;
            width: 100%;
            min-width: 80px;
        }

        .bar-fill {
            background: linear-gradient(90deg, #059669, #34d399);
            border-radius: 20px;
            height: 8px;
        }

        .rank-badge {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            text-align: center;
            line-height: 20px;
            font-size: 9px;
            font-weight: 700;
            color: #fff;
        }

        .rank-1 {
            background: #f59e0b;
        }

        .rank-2 {
            background: #94a3b8;
        }

        .rank-3 {
            background: #b45309;
        }

        .rank-n {
            background: #cbd5e1;
            color: #475569;
        }

        .footer {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #d1fae5;
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
            color: #059669;
            font-weight: 700;
        }
    </style>
</head>

<body>

    <div class="page-header">
        <h1>Equipment Usage Report</h1>
        <div class="subtitle">Usage statistics per equipment in the selected period</div>
        <div class="meta">
            <span>Period:</span> {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
        </div>
    </div>

    @php
    $total = $rows->sum('reservations_count');
    $maxVal = $rows->max('reservations_count') ?: 1;
    $top = $rows->sortByDesc('reservations_count')->first();
    @endphp

    <div class="stats-bar">
        <div class="stat-item">
            <div class="val">{{ $rows->count() }}</div>
            <div class="lbl">Total Equipment</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="val">{{ $total }}</div>
            <div class="lbl">Total Reservations</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="val">{{ $rows->count() > 0 ? round($total / $rows->count(), 1) : 0 }}</div>
            <div class="lbl">Avg per Equipment</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="val">{{ $top ? $top->reservations_count : 0 }}</div>
            <div class="lbl">Most Used</div>
        </div>
    </div>

    <div class="content">
        <div class="section-title">Equipment Usage Ranking</div>
        <table>
            <thead>
                <tr>
                    <th style="width:40px">Rank</th>
                    <th>Equipment</th>
                    <th style="width:130px">Total Reservations</th>
                    <th style="width:180px">Usage</th>
                    <th style="width:60px">Share</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows->sortByDesc('reservations_count')->values() as $i => $eq)
                @php
                $rank = $i + 1;
                $rankClass = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : 'rank-n'));
                $pct = $total > 0 ? round($eq->reservations_count / $total * 100, 1) : 0;
                $barWidth = $maxVal > 0 ? round($eq->reservations_count / $maxVal * 100) : 0;
                @endphp
                <tr>
                    <td style="text-align:center"><span class="rank-badge {{ $rankClass }}">{{ $rank }}</span></td>
                    <td><strong>{{ $eq->name }}</strong></td>
                    <td style="text-align:center"><strong>{{ $eq->reservations_count }}</strong></td>
                    <td>
                        <div class="bar-container">
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