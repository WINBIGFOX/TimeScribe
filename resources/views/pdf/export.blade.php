<!DOCTYPE html>
<html lang="en">
<head>
    <title>PDF Export</title>
    <meta charset="utf-8" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #0f172a;
            padding: 1cm;
        }

        .page-header {
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 2px solid #a1b1b3;
        }

        .page-header h1 {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
        }

        .page-header .meta {
            margin-top: 4px;
            font-size: 8px;
            color: #64748b;
        }

        .page-header .total-hours {
            margin-top: 6px;
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }

        .page-header .total-hours span {
            color: #0f172a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background-color: #a1b1b3;
        }

        thead th {
            padding: 5px 6px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            color: #0f172a;
            white-space: nowrap;
        }

        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        tbody td {
            padding: 4px 6px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
        }


        .text-right {
            text-align: right;
        }

        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 7px;
            color: #94a3b8;
            text-align: center;
            padding: 6px 1cm;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="page-footer">
        TimeScribe Export &mdash; {{ now()->format('d/m/Y H:i') }}
    </div>

    <div class="page-header">
        <div class="meta">
            @if($startDate || $endDate)
                Period:
                {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : '…' }}
                &ndash;
                {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : '…' }}
                &nbsp;&nbsp;
            @endif
            @if($projectName)
                Project: {{ $projectName }}&nbsp;&nbsp;
            @endif
            {{ $timestamps->count() }} {{ $timestamps->count() === 1 ? 'entry' : 'entries' }}
        </div>
        <div class="total-hours">Total: <span>{{ $totalHours }} h</span></div>
    </div>

    <table>
        <thead>
            <tr>
                @if($columns['type'] ?? true)<th>Type</th>@endif
                @if($columns['project'] ?? true)<th>Project</th>@endif
                @if($columns['description'] ?? true)<th>Description</th>@endif
                @if($columns['start_date'] ?? true)<th>Start Date</th>@endif
                @if($columns['start_time'] ?? true)<th>Start Time</th>@endif
                @if($columns['end_date'] ?? true)<th>End Date</th>@endif
                @if($columns['end_time'] ?? true)<th>End Time</th>@endif
                @if($columns['duration'] ?? true)<th>Duration</th>@endif
                @if($columns['import_source'] ?? true)<th>Source</th>@endif
                @if($columns['hourly_rate'] ?? true)<th class="text-right">Rate</th>@endif
                @if($columns['billable_amount'] ?? true)<th class="text-right">Amount</th>@endif
                @if($columns['currency'] ?? true)<th>Currency</th>@endif
                @if($columns['paid'] ?? true)<th>Paid</th>@endif
            </tr>
        </thead>
        <tbody>
            @forelse($timestamps as $timestamp)
                <tr>
                    @if($columns['type'] ?? true)
                    <td>
                        <span class="badge {{ $timestamp->type->value === 'work' ? 'badge-work' : 'badge-break' }}">
                            {{ ucfirst($timestamp->type->value) }}
                        </span>
                    </td>
                    @endif
                        @if($columns['project'] ?? true)
                            <td>
                                @if($timestamp->project)
                                    {{ $timestamp->project->icon }} {{ $timestamp->project->name }}
                                @endif
                            </td>
                        @endif
                        @if($columns['description'] ?? true)
                            <td>{{ $timestamp->description ?? '' }}</td>
                        @endif
                    @if($columns['start_date'] ?? true)
                    <td>{{ $timestamp->started_at->format('d/m/Y') }}</td>
                    @endif
                    @if($columns['start_time'] ?? true)
                    <td>{{ $timestamp->started_at->format('H:i') }}</td>
                    @endif
                    @if($columns['end_date'] ?? true)
                    <td>{{ $timestamp->ended_at?->format('d/m/Y') ?? '' }}</td>
                    @endif
                    @if($columns['end_time'] ?? true)
                    <td>{{ $timestamp->ended_at?->format('H:i') ?? '' }}</td>
                    @endif
                    @if($columns['duration'] ?? true)
                    <td>
                        @if($timestamp->ended_at)
                            {{ gmdate('H:i', (int) $timestamp->started_at->diffInSeconds($timestamp->ended_at)) }}
                        @endif
                    </td>
                    @endif
                    @if($columns['import_source'] ?? true)
                    <td>{{ $timestamp->source ?? '' }}</td>
                    @endif
                    @if($columns['hourly_rate'] ?? true)
                    <td class="text-right">
                        @if($timestamp->project?->hourly_rate)
                            {{ number_format($timestamp->project->hourly_rate, 2) }}
                        @endif
                    </td>
                    @endif
                    @if($columns['billable_amount'] ?? true)
                    <td class="text-right">
                        @if($timestamp->duration && $timestamp->project?->hourly_rate)
                            {{ number_format($timestamp->duration / 60 / 60 * $timestamp->project->hourly_rate, 2) }}
                        @endif
                    </td>
                    @endif
                    @if($columns['currency'] ?? true)
                    <td>{{ $timestamp->project?->hourly_rate ? ($timestamp->project->currency ?? '') : '' }}</td>
                    @endif
                    @if($columns['paid'] ?? true)
                    <td>{{ $timestamp->paid ? '✓' : '' }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align: center; padding: 20px; color: #94a3b8;">
                        No entries found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
