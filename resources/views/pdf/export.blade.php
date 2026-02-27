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
            color: black;
            padding: 1cm;
        }

        .page-header {
            margin-bottom: 16px;
            padding-bottom: 6px;
        }

        .page-header-top {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .page-header-top td {
            vertical-align: middle;
        }

        .page-header-top .logo-cell {
            padding-left: 0;
            padding-right: 0;
            text-align: start;
            width: 28px;
        }

        .page-header-top .title-cell {
            height: 24px;
        }

        .page-header .logo {
            width: 26px;
            height: 26px;
        }

        .page-header h1 {
            font-size: 18px;
            line-height: 18px;
            font-weight: bold;
            color: black;
        }

        .page-header .meta {
            margin-top: 6px;
            font-size: 8px;
            line-height: 1.2;
            color: #4d4d4d;
            border-bottom: 2px solid #d9d9d9;
            padding-top: 2px;
            padding-bottom: 2px;
        }

        .page-header .meta > table {
            margin: 0;
        }

        .meta-layout {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .meta-layout td {
            border: none;
            vertical-align: middle;
        }

        .meta-cell-left {
            width: 58%;
            padding-top: 6px;
            padding-bottom: 4px;
        }

        .meta-cell-right {
            width: 42%;
            text-align: right;
            white-space: nowrap;
            padding-top: 6px;
            padding-bottom: 4px;
        }

        .projects-inline {
            width: auto;
            border-collapse: collapse;
        }

        .projects-inline td {
            padding: 0;
            border: none;
            vertical-align: middle;
        }

        .projects-label {
            padding-right: 6px;
            white-space: nowrap;
            padding-top: 1px;
            padding-bottom: 1px;
            line-height: 12px;
        }

        .metric-inline {
            display: inline-table;
            width: auto;
            border-collapse: collapse;
            vertical-align: middle;
        }

        .metric-inline td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .metric-icon-box {
            display: block;
            width: 22px;
            height: 22px;
            line-height: 0;
            border-radius: 4px;
            margin-right: 4px;
        }

        .metric-icon {
            display: block;
            width: 14px;
            height: 14px;
            margin: 4px;
            color: black;
        }

        .metric-value {
            border: none;
            vertical-align: middle;
            padding: 0;
            font-size: 12px;
            line-height: 22px;
            font-weight: bold;
        }

        .date-range-cell {
            padding-right: 0;
            vertical-align: middle;
        }

        .date-range-inline {
            width: auto;
            margin-left: auto;
            border-collapse: collapse;
        }

        .date-range-inline td {
            padding: 0;
            padding-left: 4px;
            border: none;
            vertical-align: middle;
        }

        .date-range-icon-cell {
            padding-right: 6px;
        }

        .date-range-icon {
            display: block;
            width: 12px;
            height: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background-color: #d9d9d9;
        }

        thead th {
            padding: 4px 6px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            color: black;
            white-space: nowrap;
        }

        tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        tbody td {
            padding: 4px 6px;
            border-bottom: 1px solid #d9d9d9;
            vertical-align: top;
        }

        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 8px;
            line-height: 12px;
            vertical-align: middle;
            margin-right: 4px;
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
            color: #4d4d4d;
            text-align: center;
            padding: 6px 1cm;
            border-top: 1px solid #d9d9d9;
        }
    </style>
</head>
<body>
@php
    $logoPath = public_path('icon.svg');
    $primaryColor = '#00BBD0';
    $logoDataUri = null;
    $secToTime = static function (mixed $seconds): string {
        if (is_string($seconds)) {
            $trimmedSeconds = trim($seconds);
            if (preg_match('/^\d+:\d{2}$/', $trimmedSeconds) === 1) {
                return $trimmedSeconds;
            }
        }

        if (! is_numeric($seconds)) {
            return '0:00';
        }

        $totalSeconds = max((int) $seconds, 0);
        $hours = intdiv($totalSeconds, 3600);
        $minutes = intdiv($totalSeconds % 3600, 60);

        return sprintf('%d:%02d', $hours, $minutes);
    };

    if (file_exists($logoPath)) {
        $logoSvg = file_get_contents($logoPath);

        if ($logoSvg !== false) {
            $logoSvg = preg_replace('/fill="[^"]*"/', 'fill="'.$primaryColor.'"', $logoSvg) ?? $logoSvg;
            $logoDataUri = 'data:image/svg+xml;base64,'.base64_encode($logoSvg);
        }
    }
@endphp

<div class="page-footer">
    TimeScribe Export &mdash; {{ now()->isoFormat('lll') }}
</div>

<div class="page-header">
    <table class="page-header-top">
        <tr>
            <td class="logo-cell">
                @if($logoDataUri)
                    <img src="{{ $logoDataUri }}" alt="TimeScribe Logo" class="logo">
                @endif
            </td>
            <td class="title-cell">
                <h1>TimeScribe Export</h1>
            </td>
            @if($startDate || $endDate)
                <td class="text-right date-range-cell">
                    <table class="date-range-inline">
                        <tr>
                            <td class="date-range-icon-cell">
                                <img
                                        src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJjdXJyZW50Q29sb3IiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cmVjdCB3aWR0aD0iMTgiIGhlaWdodD0iMTgiIHg9IjMiIHk9IjQiIHJ4PSIyIi8+PHBhdGggZD0iTTE2IDJ2NCIvPjxwYXRoIGQ9Ik0zIDEwaDE4Ii8+PHBhdGggZD0iTTggMnY0Ii8+PHBhdGggZD0iTTE3IDE0aC02Ii8+PHBhdGggZD0iTTEzIDE4SDciLz48cGF0aCBkPSJNNyAxNGguMDEiLz48cGF0aCBkPSJNMTcgMThoLjAxIi8+PC9zdmc+"
                                        alt=""
                                        class="date-range-icon"
                                />
                            </td>
                            <td>
                                {{ $startDate ?? '…' }}
                                &ndash;
                                {{ $endDate ?? '…' }}
                            </td>
                        </tr>
                    </table>
                </td>
            @endif
        </tr>
    </table>
    @if ($workTime > 0 ||$breakTime > 0 || count($projects) > 0)
        <div class="meta">
            <table class="meta-layout">
                <tr>
                    <td class="meta-cell-left"
                        style="border:none; vertical-align: middle; padding-top: 8px; padding-bottom: 4px;">
                        @if(count($projects))
                            <table class="projects-inline" style="display: inline-table; width: auto;">
                                <tr>
                                    <td class="projects-label" style="padding-right: 4px">{{ __('app.projects') }}:</td>
                                    <td>
                                        @foreach($projects as $project)
                                            <span class="badge"
                                                  style="display: inline-block; position: relative; overflow: hidden; padding: 1px 6px 1px 8px; vertical-align: middle; background-color: {{ $project->color }}44; color: black;">
                                                <span style="position: absolute; top: 0; left: 0; height: 100%; width: 4px; background-color: {{ $project->color }};"></span>
                                                <span style="display: inline-block; line-height: 12px; vertical-align: middle;">{{ $project->name }}</span>
                                            </span>
                                        @endforeach
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </td>
                    <td class="meta-cell-right"
                        style="text-align: right; border:none; padding-top: 8px; padding-bottom: 4px;">
                        @if($workTime > 0)
                            <table class="metric-inline" style="display: inline-table; width: auto;">
                                <tr>
                                    <td style="border: none; padding: 0;">
                                        <div class="metric-icon-box"
                                             style="margin-right: 4px; display:inline-block; width:22px; height:22px; line-height:0; border-radius:4px; background:#00BBD0; vertical-align:middle;">
                                            <img
                                                    src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJjdXJyZW50Q29sb3IiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNMTIgMTJoLjAxIi8+PHBhdGggZD0iTTE2IDZWNGEyIDIgMCAwIDAtMi0yaC00YTIgMiAwIDAgMC0yIDJ2MiIvPjxwYXRoIGQ9Ik0yMiAxM2ExOC4xNSAxOC4xNSAwIDAgMS0yMCAwIi8+PHJlY3Qgd2lkdGg9IjIwIiBoZWlnaHQ9IjE0IiB4PSIyIiB5PSI2IiByeD0iMiIvPjwvc3ZnPg=="
                                                    alt=""
                                                    class="metric-icon"
                                                    style="display:block; width:14px; height:14px; margin:4px; color: black;"
                                            />
                                        </div>
                                    </td>
                                    <td class="metric-value"
                                        style="border: none; vertical-align: middle; padding: 0; font-size: 12px; line-height: 12px; font-weight: bold;">
                                        {{ $secToTime($workTime) }} {{ __('app.h') }}
                                    </td>
                                </tr>
                            </table>
                        @endif
                        @if($breakTime > 0)
                            <table class="metric-inline" style="display: inline-table; width: auto; margin-left: 10px">
                                <tr>
                                    <td style="border: none; padding: 0;">
                                        <div class="metric-icon-box"
                                             style="margin-right: 4px; display:inline-block; width:22px; height:22px; line-height:0; border-radius:4px; background:#FB64B6; vertical-align:middle;">
                                            <img
                                                    src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJjdXJyZW50Q29sb3IiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNMTAgMnYyIi8+PHBhdGggZD0iTTE0IDJ2MiIvPjxwYXRoIGQ9Ik0xNiA4YTEgMSAwIDAgMSAxIDF2OGE0IDQgMCAwIDEtNCA0SDdhNCA0IDAgMCAxLTQtNFY5YTEgMSAwIDAgMSAxLTFoMTRhNCA0IDAgMSAxIDAgOGgtMSIvPjxwYXRoIGQ9Ik02IDJ2MiIvPjwvc3ZnPg=="
                                                    alt=""
                                                    class="metric-icon"
                                                    style="display:block; width:14px; height:14px; margin:4px; color: black;"
                                            />
                                        </div>
                                    </td>
                                    <td class="metric-value"
                                        style="border: none; vertical-align: middle; padding: 0; font-size: 12px; line-height: 12px; font-weight: bold;">
                                        {{ $secToTime($breakTime) }} {{ __('app.h') }}
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endif
</div>
<table>
    <thead>
    <tr>
        @foreach($columns as $column)
            <th>{{ $column }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @forelse($timestamps as $timestampColumns)
        <tr>
            @foreach($timestampColumns as $column)
                <td>{{ $column }}</td>
            @endforeach
        </tr>
    @empty
        <tr>
            <td colspan="13" style="text-align: center; padding: 20px; color: #4d4d4d;">
                {{ __('app.no times available')  }}
            </td>
        </tr>
    @endforelse
    </tbody>
</table>
</body>
</html>
