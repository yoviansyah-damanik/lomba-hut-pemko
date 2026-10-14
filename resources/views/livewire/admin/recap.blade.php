@php
    $typeColors = [
        'SD' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
        'SMP' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300',
        'SMA' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',
        'Umum' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    ];
@endphp

<div class="space-y-4" x-data x-on:trigger-print.window="setTimeout(() => window.print(), 150)">
    @if (!$competition)
        <div
            class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-10 text-center text-gray-500 dark:text-gray-400">
            {{ __('Lomba tidak ditemukan.') }}
        </div>
    @else
        <div class="flex items-start justify-between gap-3 print:hidden">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $competition->name }}</h3>
                    @if ($competition->isActive())
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 shrink-0">
                            <span class="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse"></span>
                            {{ __('Berlangsung') }}
                        </span>
                    @endif
                    @if ($maskIdentity)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 text-xs font-medium">
                            {{ __('Identitas Disamarkan') }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $competition->start_time->translatedFormat('d M Y H:i') }} &ndash;
                    {{ $competition->end_time->translatedFormat('d M Y H:i') }}
                    &middot; {{ __(':count peserta', ['count' => $rows->count()]) }}
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ __('Cetak') }}
                </button>
                <button type="button" wire:click="printAllTypes" title="{{ __('Cetak seluruh jenjang (SD, SMP, SMA) sekaligus, terlepas dari filter yang aktif') }}"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ __('Cetak Semua Jenjang') }}
                </button>
                @if ($school_type !== '')
                    <button type="button" wire:click="openBeritaAcara"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700">
                        {{ __('Cetak Berita Acara') }}
                    </button>
                @endif
                <x-primary-button type="button" wire:click="downloadPdf" wire:loading.attr="disabled"
                    class="uppercase tracking-widest text-xs">
                    <span wire:loading.remove wire:target="downloadPdf">{{ __('Unduh PDF') }}</span>
                    <span wire:loading wire:target="downloadPdf">{{ __('Menyiapkan...') }}</span>
                </x-primary-button>
            </div>
        </div>

        <div class="hidden print:block mb-4 pb-3 border-b-2 border-gray-800">
            <div class="flex items-center gap-3">
                <img src="{{ Vite::asset('resources/images/logo.png') }}" alt="{{ config('app.name') }}"
                    class="h-12 w-12 object-contain">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900">{{ config('app.name') }}</p>
                    <h1 class="text-xl font-bold text-gray-900 uppercase tracking-wide">{{ __('Rekap Nilai Lomba') }}
                    </h1>
                </div>
            </div>
            <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-gray-700">
                <div class="flex gap-1">
                    <dt class="font-semibold">{{ __('Lomba') }}:</dt>
                    <dd>{{ $competition->name }}</dd>
                </div>
                <div class="flex gap-1">
                    <dt class="font-semibold">{{ __('Jenjang') }}:</dt>
                    <dd>{{ $school_type ?: __('Semua') }}</dd>
                </div>
                <div class="flex gap-1">
                    <dt class="font-semibold">{{ __('Periode') }}:</dt>
                    <dd>{{ $competition->start_time->translatedFormat('d M Y H:i') }} &ndash;
                        {{ $competition->end_time->translatedFormat('d M Y H:i') }}</dd>
                </div>
                <div class="flex gap-1">
                    <dt class="font-semibold">{{ __('Jumlah Peserta') }}:</dt>
                    <dd>{{ $rows->count() }}</dd>
                </div>
                <div class="flex gap-1 col-span-2">
                    <dt class="font-semibold">{{ __('Dicetak') }}:</dt>
                    <dd>{{ now()->translatedFormat('d M Y H:i') }}</dd>
                </div>
            </dl>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 print:hidden">
            <div class="flex flex-wrap gap-2">
                <button wire:click="$set('school_type', '')"
                    class="px-3 py-1.5 rounded-full text-sm font-medium {{ $school_type === '' ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700' }}">
                    {{ __('Semua') }}
                </button>
                @foreach (\App\Livewire\Admin\Recap::SCHOOL_TYPES as $type)
                    <button wire:click="$set('school_type', '{{ $type }}')"
                        class="px-3 py-1.5 rounded-full text-sm font-medium {{ $school_type === $type ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700' }}">
                        {{ $type }}
                    </button>
                @endforeach
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" wire:model.live="include_notes"
                    class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-red-600 shadow-sm focus:ring-red-500">
                {{ __('Sertakan catatan juri saat cetak') }}
            </label>
        </div>

        @if ($rows->isEmpty())
            <div
                class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-10 text-center text-gray-500 dark:text-gray-400">
                {{ __('Belum ada peserta terdaftar untuk lomba ini.') }}
            </div>
        @else
            <div
                class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl overflow-x-auto print:overflow-visible print:shadow-none print:ring-0">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 dark:border-gray-700 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                            <th class="px-4 py-3 whitespace-nowrap">{{ __('Peringkat') }}</th>
                            <th class="px-4 py-3 whitespace-nowrap">{{ __('Peserta') }}</th>
                            <th class="px-4 py-3 whitespace-nowrap">{{ __('Jenjang') }}</th>
                            @foreach ($competition->criteria as $criterion)
                                <th class="px-4 py-3 text-right whitespace-nowrap"
                                    title="{{ $criterion->description }}">
                                    <div>{{ $criterion->name }}</div>
                                    @if ($criterion->hasItems())
                                        <span class="inline-block text-[10px] font-normal text-gray-400 dark:text-gray-500 lowercase print:hidden">
                                            ({{ __(':count sub-item', ['count' => $criterion->items->count()]) }})
                                        </span>
                                    @endif
                                </th>
                            @endforeach
                            <th class="px-4 py-3 text-right whitespace-nowrap" title="{{ __('Akumulasi nilai murni dari seluruh juri') }}">{{ __('Nilai Murni') }}</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap" title="{{ __('Penambahan nilai bonus') }}">{{ __('(+)') }}</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap" title="{{ __('Pengurangan nilai penalti/potongan') }}">{{ __('(-)') }}</th>
                            <th class="px-4 py-3 text-right whitespace-nowrap">{{ __('Nilai Akhir') }}</th>
                            <th class="px-4 py-3 whitespace-nowrap">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap print:hidden">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($rows as $row)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">
                                    {{ \App\Livewire\Admin\Recap::rankLabel($loop->iteration) }}
                                    @if ($row->is_tied)
                                        <button type="button"
                                            wire:click="openTieOrder('{{ $row->registration->school_type }}', {{ (int) $row->total_score }})"
                                            class="block mt-0.5 text-[10px] font-normal text-amber-600 dark:text-amber-400 underline decoration-dotted print:hidden">
                                            {{ __('Atur urutan') }}
                                        </button>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $maskIdentity ? __('Peserta :rank', ['rank' => $loop->iteration]) : $row->registration->displayName() }}
                                        </p>
                                        <button type="button"
                                            wire:click="viewJudgeDetail('{{ $row->registration->id }}')"
                                            class="shrink-0 text-gray-400 hover:text-red-600 dark:hover:text-red-400 print:hidden"
                                            title="{{ __('Lihat detail penilaian juri') }}">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                    @if (! $maskIdentity)
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $row->npp }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $typeColors[$row->registration->school_type] }}">
                                        {{ $row->registration->school_type }}
                                    </span>
                                </td>
                                @foreach ($competition->criteria as $criterion)
                                    @php $score = $row->criteria_scores[$criterion->id]; @endphp
                                    @if (!$score['applicable'])
                                        <td class="px-4 py-3 text-right text-gray-300 dark:text-gray-600">&mdash;</td>
                                    @else
                                        <td
                                            class="px-4 py-3 text-right align-top {{ $score['expected'] > 0 && $score['submitted'] < $score['expected'] ? 'text-amber-600 dark:text-amber-400' : 'text-gray-700 dark:text-gray-300' }}">
                                            @if (!empty($score['has_items']) && !empty($score['sub_items']) && $score['sub_items']->isNotEmpty())
                                                <div class="flex flex-col items-end">
                                                    <div class="space-y-1 text-xs text-gray-500 dark:text-gray-400 w-full min-w-[140px] pb-1.5 border-b border-gray-100 dark:border-gray-700/60 mb-1.5 print:hidden">
                                                        @foreach ($score['sub_items'] as $subItem)
                                                            <div class="flex items-center justify-between gap-3">
                                                                <span class="truncate max-w-[120px] text-[11px]" title="{{ $subItem['name'] }}">{{ $subItem['name'] }}</span>
                                                                <span class="font-medium text-gray-700 dark:text-gray-300 shrink-0 text-xs">{{ $subItem['score'] }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="flex items-center justify-end gap-1 font-semibold text-gray-900 dark:text-gray-100">
                                                        <span class="text-[10px] uppercase tracking-wider text-gray-400 font-normal print:hidden">{{ __('Total') }}:</span>
                                                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $score['score'] }}</span>
                                                        @if ($score['expected'] > 0 && $score['submitted'] < $score['expected'])
                                                            <span class="text-amber-500 font-bold"
                                                                title="{{ __(':submitted/:expected juri menilai', ['submitted' => $score['submitted'], 'expected' => $score['expected']]) }}">*</span>
                                                        @endif
                                                        @if ($score['notes']->isNotEmpty())
                                                            <div class="relative print:hidden" x-data="{ open: false }"
                                                                @click.outside="open = false">
                                                                <button type="button" @click="open = !open"
                                                                    class="shrink-0 text-gray-400 hover:text-red-600 dark:hover:text-red-400"
                                                                    aria-label="{{ __('Lihat catatan juri') }}">
                                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                                        stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                                    </svg>
                                                                </button>
                                                                <div x-show="open" x-transition
                                                                    class="absolute z-10 right-0 top-full mt-1 w-56 rounded-lg bg-gray-900 dark:bg-gray-700 text-white text-[11px] leading-snug p-2.5 shadow-lg normal-case font-normal text-left"
                                                                    style="display: none;">
                                                                    @foreach ($score['notes'] as $note)
                                                                        <p class="mb-1.5 last:mb-0"><span
                                                                                class="font-semibold">{{ $note['judge'] }}:</span>
                                                                            {{ $note['text'] }}</p>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="flex items-center justify-end gap-1">
                                                    {{ $score['score'] }}
                                                    @if ($score['expected'] > 0 && $score['submitted'] < $score['expected'])
                                                        <span
                                                            title="{{ __(':submitted/:expected juri menilai', ['submitted' => $score['submitted'], 'expected' => $score['expected']]) }}">*</span>
                                                    @endif
                                                    @if ($score['notes']->isNotEmpty())
                                                        <div class="relative print:hidden" x-data="{ open: false }"
                                                            @click.outside="open = false">
                                                            <button type="button" @click="open = !open"
                                                                class="shrink-0 text-gray-400 hover:text-red-600 dark:hover:text-red-400"
                                                                aria-label="{{ __('Lihat catatan juri') }}">
                                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                                    stroke="currentColor" stroke-width="2">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                                </svg>
                                                            </button>
                                                            <div x-show="open" x-transition
                                                                class="absolute z-10 right-0 top-full mt-1 w-56 rounded-lg bg-gray-900 dark:bg-gray-700 text-white text-[11px] leading-snug p-2.5 shadow-lg normal-case font-normal text-left"
                                                                style="display: none;">
                                                                @foreach ($score['notes'] as $note)
                                                                    <p class="mb-1.5 last:mb-0"><span
                                                                            class="font-semibold">{{ $note['judge'] }}:</span>
                                                                        {{ $note['text'] }}</p>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                            @if ($include_notes && $score['notes']->isNotEmpty())
                                                <div
                                                    class="mt-1 text-left text-[10px] leading-snug font-normal normal-case text-gray-400 dark:text-gray-500 max-w-[12rem] whitespace-normal ms-auto">
                                                    @foreach ($score['notes'] as $note)
                                                        <p>{{ $note['judge'] }}: {{ $note['text'] }}</p>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                                <td class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300">
                                    {{ $row->raw_score }}
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @if ($row->score_addition > 0)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 ring-1 ring-emerald-200 dark:ring-emerald-800"
                                            title="{{ $row->adjustment_notes ?: __('Penambahan nilai') }}">
                                            +{{ $row->score_addition }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">0</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @if ($row->score_deduction > 0)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 ring-1 ring-rose-200 dark:ring-rose-800"
                                            title="{{ $row->adjustment_notes ?: __('Pengurangan nilai') }}">
                                            -{{ $row->score_deduction }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">0</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-lg font-bold text-red-600 dark:text-red-400">
                                    {{ $row->final_score }}
                                    @if (filled($row->adjustment_notes))
                                        <p class="text-[10px] font-normal text-gray-400 dark:text-gray-500 truncate max-w-[8rem] ms-auto"
                                            title="{{ $row->adjustment_notes }}">
                                            {{ $row->adjustment_notes }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($row->status === 'complete')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            {{ __('Lengkap') }}
                                        </span>
                                    @elseif ($row->status === 'partial')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300"
                                            title="{{ __(':submitted dari :expected penilaian juri masuk', ['submitted' => $row->submitted_count, 'expected' => $row->expected_count]) }}">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            {{ __('Belum lengkap') }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                            {{ __('Belum dinilai') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center print:hidden">
                                    <button type="button"
                                        wire:click="openAdjustment('{{ $row->registration->id }}')"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                                        title="{{ __('Sesuaikan nilai tambah/kurang') }}">
                                        <svg class="w-3.5 h-3.5 mr-1 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                        </svg>
                                        {{ __('Sesuaikan') }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="text-xs text-gray-400 dark:text-gray-500">
                {{ __('* Nilai kriteria belum dinilai oleh seluruh juri yang ditugaskan.') }}
                @if (!$include_notes)
                    <br>{{ __('Klik ikon catatan pada nilai untuk melihat catatan juri, atau centang "Sertakan catatan juri saat cetak" untuk menampilkannya langsung.') }}
                @endif
            </p>
        @endif

        <p class="hidden print:block mt-6 pt-3 border-t border-gray-300 text-center text-[10px] text-gray-400">
            {{ __('Dicetak melalui :app (:url) pada :time', ['app' => config('app.name'), 'url' => config('app.url'), 'time' => now()->translatedFormat('d M Y H:i')]) }}
        </p>

        <x-simple-modal :show="(bool) $tieClusterKey" show-property="tieClusterKey" max-width="max-w-3xl">
            @if ($tieCluster)
                <form wire:submit="saveTieOrder" class="space-y-4">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Atur Urutan Peringkat') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __(':count peserta memiliki skor total yang sama (:score). Tentukan urutan peringkat 1..:count.', [
                            'count' => $tieCluster->count(),
                            'score' => $tieCluster->first()->total_score,
                        ]) }}
                    </p>
                    @if ($maskIdentity)
                        <p class="text-xs text-amber-600 dark:text-amber-400">
                            {{ __('Identitas peserta disamarkan. Gunakan NPP untuk mencocokkan dengan catatan penilaian Anda.') }}
                        </p>
                    @endif

                    <div class="space-y-2">
                        @foreach ($tieCluster as $registration)
                            <div class="flex items-center gap-3">
                                <input type="number" min="1" max="{{ $tieCluster->count() }}"
                                    wire:model="tiePositions.{{ $registration->id }}"
                                    class="w-16 text-sm text-center border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300 truncate">
                                    {{ $maskIdentity ? __('Peserta') : $registration->displayName() }}
                                    <span class="text-xs text-gray-400">({{ $registration->npp }})</span>
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <x-input-error :messages="$errors->get('tiePositions')" class="mt-2" />

                    <div class="flex items-center justify-between pt-2">
                        <button type="button" wire:click="resetTieOrder"
                            wire:confirm="{{ __('Kembalikan kelompok ini ke urutan otomatis (alfabetis)?') }}"
                            class="text-xs font-medium text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                            {{ __('Reset ke otomatis') }}
                        </button>
                        <div class="flex gap-2">
                            <x-secondary-button type="button"
                                wire:click="closeTieOrder">{{ __('Batal') }}</x-secondary-button>
                            <x-primary-button type="submit">{{ __('Simpan Urutan') }}</x-primary-button>
                        </div>
                    </div>
                </form>
            @endif
        </x-simple-modal>

        <x-simple-modal :show="$showBeritaAcaraModal" show-property="showBeritaAcaraModal">
            <form wire:submit="downloadBeritaAcara" class="space-y-4">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Cetak Berita Acara') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Dokumen resmi berisi Juara I-III dan Harapan I-III untuk jenjang :type pada :name.', ['type' => $school_type, 'name' => $competition->name]) }}
                </p>

                <div>
                    <x-input-label for="beritaAcaraTempat" :value="__('Tempat')" />
                    <x-text-input wire:model="beritaAcaraTempat" id="beritaAcaraTempat" class="block mt-1 w-full"
                        type="text" placeholder="{{ __('Contoh: Padangsidimpuan') }}" required autocomplete="off" />
                    <x-input-error :messages="$errors->get('beritaAcaraTempat')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="beritaAcaraTanggal" :value="__('Tanggal')" />
                    <x-text-input wire:model="beritaAcaraTanggal" id="beritaAcaraTanggal" class="block mt-1 w-full"
                        type="date" required />
                    <x-input-error :messages="$errors->get('beritaAcaraTanggal')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="beritaAcaraKoordinator" :value="__('Nama Koordinator Dewan Juri')" />
                    <x-text-input wire:model="beritaAcaraKoordinator" id="beritaAcaraKoordinator"
                        class="block mt-1 w-full" type="text" required autocomplete="off" />
                    <x-input-error :messages="$errors->get('beritaAcaraKoordinator')" class="mt-2" />
                </div>

                <div class="flex justify-end gap-2">
                    <x-secondary-button type="button"
                        wire:click="closeBeritaAcara">{{ __('Batal') }}</x-secondary-button>
                    <x-primary-button type="submit" wire:loading.attr="disabled" wire:target="downloadBeritaAcara">
                        <span wire:loading.remove wire:target="downloadBeritaAcara">{{ __('Cetak PDF') }}</span>
                        <span wire:loading wire:target="downloadBeritaAcara">{{ __('Menyiapkan...') }}</span>
                    </x-primary-button>
                </div>
            </form>
        </x-simple-modal>

        <x-simple-modal :show="(bool) $detailRegistrationId" show-property="detailRegistrationId" max-width="max-w-2xl">
            @if ($judgeDetail)
                <div class="space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 truncate">
                                {{ $maskIdentity ? __('Peserta') : $judgeDetail->registration->displayName() }}
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $judgeDetail->registration->npp }} &middot;
                                {{ $judgeDetail->registration->school_type }}
                            </p>
                        </div>
                        <span
                            class="shrink-0 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $typeColors[$judgeDetail->registration->school_type] }}">
                            {{ $judgeDetail->registration->school_type }}
                        </span>
                    </div>

                    <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
                        @forelse ($judgeDetail->criteria as $item)
                            <div class="ring-1 ring-gray-200 dark:ring-gray-700 rounded-xl p-3">
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $item->criterion->name }}
                                </p>

                                <div class="mt-2 divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse ($item->judges as $entry)
                                        <div class="py-2 flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-sm text-gray-700 dark:text-gray-300 truncate">
                                                    {{ $entry->judge->name }}
                                                </p>
                                                @if ($entry->submitted && $entry->items->isNotEmpty())
                                                    <div class="mt-1 space-y-0.5 pl-2 border-l-2 border-red-200 dark:border-red-800">
                                                        @foreach ($entry->items as $subItem)
                                                            <p class="text-[11px] text-gray-500 dark:text-gray-400 flex items-center justify-between gap-3">
                                                                <span>{{ $subItem->criterionItem?->name ?? __('Item') }}:</span>
                                                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $subItem->score }}</span>
                                                            </p>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                @if ($entry->submitted && filled($entry->notes))
                                                    <p class="text-xs italic text-gray-400 dark:text-gray-500 mt-0.5">
                                                        {{ $entry->notes }}
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="shrink-0 text-right">
                                                @if ($entry->submitted)
                                                    <span class="text-base font-bold text-red-600 dark:text-red-400">
                                                        {{ $entry->score }}
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                        {{ __('Belum menilai') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p class="py-2 text-xs text-gray-400 dark:text-gray-500">
                                            {{ __('Belum ada juri yang ditugaskan pada kriteria ini.') }}
                                        </p>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Tidak ada kriteria yang berlaku untuk jenjang peserta ini.') }}
                            </p>
                        @endforelse
                    </div>

                    <div class="flex justify-end pt-2">
                        <x-secondary-button type="button"
                            wire:click="closeJudgeDetail">{{ __('Tutup') }}</x-secondary-button>
                    </div>
                </div>
            @endif
        </x-simple-modal>

        <x-simple-modal :show="$showAdjustmentModal" max-width="max-w-md" show-property="showAdjustmentModal">
            <form wire:submit="saveAdjustment" class="space-y-4">
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Penyesuaian Nilai') }}
                    </h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $adjustingRegistrationName }}</span>
                        ({{ $adjustingRegistrationNpp }})
                    </p>
                    <p class="mt-0.5 text-[11px] text-gray-400 dark:text-gray-500">
                        {{ __('Penyesuaian ini terpisah dari akumulasi nilai murni juri dan langsung mempengaruhi nilai akhir.') }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="score_addition" :value="__('Penambahan (+)')" />
                        <x-text-input wire:model="score_addition" id="score_addition" class="block mt-1 w-full text-emerald-600 font-bold"
                            type="number" min="0" required />
                        <x-input-error :messages="$errors->get('score_addition')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="score_deduction" :value="__('Pengurangan (-)')" />
                        <x-text-input wire:model="score_deduction" id="score_deduction" class="block mt-1 w-full text-rose-600 font-bold"
                            type="number" min="0" required />
                        <x-input-error :messages="$errors->get('score_deduction')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <x-input-label for="adjustment_notes" :value="__('Keterangan / Alasan')" />
                    <textarea wire:model="adjustment_notes" id="adjustment_notes" rows="2"
                        placeholder="{{ __('Contoh: Pelanggaran batas waktu 30 detik (-5), Atribut terbaik (+2)') }}"
                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm mt-1 block w-full text-xs"></textarea>
                    <x-input-error :messages="$errors->get('adjustment_notes')" class="mt-1" />
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <x-secondary-button type="button"
                        wire:click="closeAdjustment">{{ __('Batal') }}</x-secondary-button>
                    <x-primary-button type="submit">{{ __('Simpan Penyesuaian') }}</x-primary-button>
                </div>
            </form>
        </x-simple-modal>
    @endif
</div>
