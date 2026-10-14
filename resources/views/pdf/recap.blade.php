<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>{{ __('Rekap Nilai') }} - {{ $competition->name }}</title>
    <style>
        @page {
            margin: 20px 24px 40px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1f2937;
        }

        .letterhead {
            border-bottom: 1.5px solid #1f2937;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .letterhead .app-name {
            font-size: 8px;
            font-weight: normal;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0 0 2px;
        }

        .letterhead .title {
            font-size: 19px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.1;
            color: #111827;
            margin: 0 0 4px;
        }

        .letterhead-meta {
            margin-bottom: 10px;
        }

        .meta {
            clear: both;
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .meta td {
            padding: 1px 0;
        }

        .meta td.label {
            width: 110px;
            font-weight: bold;
        }

        table.recap {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        table.recap th,
        table.recap td {
            border: 1px solid #d1d5db;
            padding: 4px 6px;
        }

        table.recap thead th {
            background-color: #f3f4f6;
            font-size: 9px;
            text-transform: uppercase;
            text-align: left;
        }

        table.recap td.number {
            text-align: right;
        }

        table.recap td.total {
            text-align: right;
            font-weight: bold;
            color: #dc2626;
        }

        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 8px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-sd {
            background-color: #e0f2fe;
            color: #0369a1;
        }

        .badge-smp {
            background-color: #ede9fe;
            color: #6d28d9;
        }

        .badge-sma {
            background-color: #ccfbf1;
            color: #0f766e;
        }

        .badge-umum {
            background-color: #fef3c7;
            color: #b45309;
        }

        .incomplete {
            color: #b45309;
        }

        .footnote {
            margin-top: 8px;
            font-size: 8px;
            color: #6b7280;
        }

        .empty {
            text-align: center;
            padding: 30px 0;
            color: #6b7280;
        }

        .letterhead .logo {
            height: 56px;
            object-fit: contain;
            float: left;
            margin-right: 12px;
        }

        .letterhead-text {
            overflow: hidden;
        }

        .letterhead-text * {
            margin: 0;
        }

        .letterhead .owner {
            font-size: 10px;
            font-weight: bold;
            font-style: normal;
            letter-spacing: 0.2px;
            color: #374151;
            margin: 4px 0 0;
            text-transform: uppercase;
        }

        .cell-notes {
            margin-top: 3px;
            font-size: 7px;
            font-weight: normal;
            text-align: left;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <div class="letterhead">
        @if ($logo)
            <img src="{{ $logo }}" class="logo" alt="{{ config('app.name') }}">
        @endif
        <div class="letterhead-text">
            <p class="app-name">{{ config('app.name') }}</p>
            <p class="title">{{ __('Rekap Nilai Lomba') }}</p>
            <p class="owner">{{ config('app.owner') }}</p>
        </div>
    </div>

    <div class="letterhead-meta">
        <table class="meta">
            <tr>
                <td class="label">{{ __('Lomba') }}</td>
                <td>: {{ $competition->name }}</td>
                <td class="label">{{ __('Jenjang') }}</td>
                <td>: {{ $school_type ?: __('Semua') }}</td>
            </tr>
            <tr>
                <td class="label">{{ __('Periode') }}</td>
                <td>: {{ $competition->start_time->translatedFormat('d M Y H:i') }} &ndash;
                    {{ $competition->end_time->translatedFormat('d M Y H:i') }}</td>
                <td class="label">{{ __('Jumlah Peserta') }}</td>
                <td>: {{ $rows->count() }}</td>
            </tr>
            <tr>
                <td class="label">{{ __('Dicetak') }}</td>
                <td colspan="3">: {{ now()->translatedFormat('d M Y H:i') }}</td>
            </tr>
        </table>
    </div>

    @if ($rows->isEmpty())
        <p class="empty">{{ __('Belum ada peserta terdaftar untuk lomba ini.') }}</p>
    @else
        @php
            $badgeClass = [
                'SD' => 'badge-sd',
                'SMP' => 'badge-smp',
                'SMA' => 'badge-sma',
                'Umum' => 'badge-umum',
            ];
        @endphp
        <table class="recap">
            <thead>
                <tr>
                    <th style="text-align:center">{{ __('Peringkat') }}</th>
                    <th style="text-align:center">{{ __('Peserta') }}</th>
                    <th style="text-align:center">{{ __('Jenjang') }}</th>
                    @foreach ($competition->criteria as $criterion)
                        <th style="text-align:center" class="number">{{ $criterion->name }}</th>
                    @endforeach
                    <th style="text-align:center" class="number">{{ __('Akhir') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td style="width:40px; text-align:center;">{{ \App\Livewire\Admin\Recap::rankLabel($loop->iteration) }}</td>
                        <td>
                            @if ($maskIdentity)
                                {{ __('Peserta :rank', ['rank' => $loop->iteration]) }}
                            @else
                                {{ $row->registration->displayName() }}<br>
                                <span style="color:#9ca3af">{{ $row->npp }}</span>
                            @endif
                        </td>
                        <td style="width:60px; text-align:center;"><span
                                class="badge {{ $badgeClass[$row->registration->school_type] }}">{{ $row->registration->school_type }}</span>
                        </td>
                        @foreach ($competition->criteria as $criterion)
                            @php $score = $row->criteria_scores[$criterion->id]; @endphp
                            @if (!$score['applicable'])
                                <td class="number" style="color:#d1d5db">&mdash;</td>
                            @else
                                <td
                                    class="number {{ $score['expected'] > 0 && $score['submitted'] < $score['expected'] ? 'incomplete' : '' }}">
                                    {{ $score['score'] }}{{ $score['expected'] > 0 && $score['submitted'] < $score['expected'] ? '*' : '' }}
                                    @if ($include_notes && $score['notes']->isNotEmpty())
                                        <div class="cell-notes">
                                            @foreach ($score['notes'] as $note)
                                                <div>{{ $note['judge'] }}: {{ $note['text'] }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            @endif
                        @endforeach
                        <td class="total">
                            {{ $row->final_score }}
                            @if (filled($row->adjustment_notes))
                                <div class="cell-notes">{{ $row->adjustment_notes }}</div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="footnote">{{ __('* Nilai kriteria belum dinilai oleh seluruh juri yang ditugaskan.') }}</p>
    @endif
</body>

</html>
