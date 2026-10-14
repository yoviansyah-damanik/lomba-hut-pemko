<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>{{ __('Berita Acara') }} - {{ $competition->name }}</title>
    <style>
        @page {
            margin: 40px 48px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #111827;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
        }

        .header .title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin: 0 0 2px;
        }

        .header .owner {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }

        .tingkat-box {
            border-top: 1.5px solid #111827;
            border-bottom: 1.5px solid #111827;
            padding: 8px 0;
            text-align: center;
            margin: 16px 0 10px;
        }

        .tingkat-box .tingkat {
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }

        .subtitle {
            text-align: center;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 6px;
            padding-bottom: 6px;
            border-bottom: 1px solid #9ca3af;
        }

        table.hasil {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        table.hasil th,
        table.hasil td {
            border: 1px solid #111827;
            padding: 8px 10px;
            text-align: center;
        }

        table.hasil thead th {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: bold;
        }

        table.hasil td.rank {
            font-weight: bold;
        }

        table.hasil td.score {
            font-weight: bold;
        }

        .signature {
            margin-top: 40px;
            width: 260px;
            margin-left: auto;
            text-align: center;
            font-size: 12px;
        }

        .signature .place-date {
            margin: 0 0 4px;
        }

        .signature .role {
            margin: 0 0 60px;
        }

        .signature .name {
            margin: 0;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="header">
        <p class="title">{{ __('Hasil Penilaian Perlombaan :name', ['name' => $competition->name]) }}</p>
        <p class="owner">{{ config('app.owner') }}</p>
    </div>

    @php
        $tingkatLabels = [
            'SD' => 'SD/MI Sederajat',
            'SMP' => 'SMP/MTs Sederajat',
            'SMA' => 'SMA/MA/SMK Sederajat',
            'Umum' => 'Kategori Umum',
        ];
    @endphp

    <div class="tingkat-box">
        <p class="tingkat">{{ __('Tingkat :label', ['label' => $tingkatLabels[$schoolType] ?? $schoolType]) }}</p>
    </div>

    <p class="subtitle">{{ __('Berita Acara Hasil Perlombaan') }}</p>

    @if ($rows->isEmpty())
        <p style="text-align:center; padding: 20px 0; color:#6b7280;">
            {{ __('Belum ada nilai yang dapat direkap untuk jenjang ini.') }}</p>
    @else
        <table class="hasil">
            <thead>
                <tr>
                    <th style="width:40px;">{{ __('No') }}</th>
                    <th style="width:80px;">{{ __('NPP') }}</th>
                    <th>{{ __('Peringkat') }}</th>
                    <th style="width:120px;">{{ __('Nilai Akhir') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>{{ $row->no }}</td>
                        <td>{{ $row->npp }}</td>
                        <td class="rank">{{ $row->rank_label }}</td>
                        <td class="score">{{ number_format($row->total_score, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="signature">
        <p class="place-date">{{ $tempat }}, {{ $tanggal->translatedFormat('d F Y') }}</p>
        <p class="role">{{ __('Koordinator Dewan Juri') }}</p>
        <p class="name">{{ $koordinator }}</p>
    </div>
</body>

</html>
