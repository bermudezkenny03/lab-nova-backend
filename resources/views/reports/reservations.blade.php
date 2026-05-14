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
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 60%, #60a5fa 100%);
            padding: 28px 32px 22px;
            margin-bottom: 0;
        }

        .page-header h1 {
            font-size: 22px;
            color: #fff;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .page-header .subtitle {
            color: #bfdbfe;
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
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
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
            color: #1e40af;
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
            background: #e2e8f0;
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
            border-bottom: 2px solid #3b82f6;
            display: inline-block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        thead tr {
            background: #1e40af;
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

        thead th:first-child {
            border-radius: 4px 0 0 0;
        }

        thead th:last-child {
            border-radius: 0 4px 0 0;
        }

        tbody tr:nth-child(even) td {
            background: #f1f5f9;
        }

        tbody tr:hover td {
            background: #e0f2fe;
        }

        tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            font-size: 9.5px;
            vertical-align: middle;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 8.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .badge-pending {
            background: #fef9c3;
            color: #854d0e;
        }

        .badge-approved {
            background: #dcfce7;
            color: #166534;
        }

        .badge-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-cancelled {
            background: #f1f5f9;
            color: #475569;
        }

        .badge-completed {
            background: #dbeafe;
            color: #1e40af;
        }

        .footer {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
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
            color: #3b82f6;
            font-weight: 700;
        }
    </style>
</head>

<body>

    <div class="page-header">
        <h1>Reservations Report</h1>
        <div class="subtitle">Detailed listing of all reservations in the selected period</div>
        <div class="meta">
            <span>Period:</span> {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
        </div>
    </div>

    <div class="stats-bar">
        <div class="stat-item">
            <div class="val">{{ $rows->count() }}</div>
            <div class="lbl">Total Reservations</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="val">{{ $rows->where('reservation_status.slug', 'completed')->count() }}</div>
            <div class="lbl">Completed</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="val">{{ $rows->where('reservation_status.slug', 'pending')->count() }}</div>
            <div class="lbl">Pending</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="val">{{ $rows->where('reservation_status.slug', 'cancelled')->count() }}</div>
            <div class="lbl">Cancelled</div>
        </div>
    </div>

    <div class="content">
        <div class="section-title">Reservation Details</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Equipment</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                <tr>
                    <td><strong>{{ $r->id }}</strong></td>
                    <td>{{ trim(($r->user->name ?? '') . ' ' . ($r->user->last_name ?? '')) }}</td>
                    <td>{{ $r->equipment->name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->start_time)->format('M d, Y H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->end_time)->format('M d, Y H:i') }}</td>
                    <td>
                        @php $slug = $r->reservation_status->slug ?? '' @endphp
                        <span class="badge badge-{{ $slug }}">{{ $r->reservation_status->name ?? '—' }}</span>
                    </td>
                    <td>{{ $r->notes ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:20px;color:#94a3b8;">No records found for this period.</td>
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