@php
    $typeColors = [
        'SD' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
        'SMP' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300',
        'SMA' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',
        'Umum' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    ];
@endphp

<div class="space-y-4">
    @if ($competitions->isEmpty())
        <div
            class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-10 text-center text-gray-500 dark:text-gray-400">
            {{ __('Anda belum memiliki kriteria yang ditugaskan pada lomba manapun.') }}
        </div>
    @else
        <div class="flex flex-wrap gap-2">
            @foreach ($competitions as $item)
                <button type="button" wire:click="$set('competitionId', '{{ $item->id }}')"
                    class="px-3 py-1.5 rounded-full text-sm font-medium {{ $competitionId === $item->id ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700' }}">
                    {{ $item->name }}
                </button>
            @endforeach
        </div>

        @if (!$competition)
            <div
                class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-10 text-center text-gray-500 dark:text-gray-400">
                {{ __('Lomba tidak ditemukan.') }}
            </div>
        @else
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $competition->name }}</h3>
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

                @if ($availableSchoolTypes->count() > 1)
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="$set('school_type', '')"
                            class="px-3 py-1.5 rounded-full text-sm font-medium {{ $school_type === '' ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700' }}">
                            {{ __('Semua') }}
                        </button>
                        @foreach ($availableSchoolTypes as $type)
                            <button type="button" wire:click="$set('school_type', '{{ $type }}')"
                                class="px-3 py-1.5 rounded-full text-sm font-medium {{ $school_type === $type ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700' }}">
                                {{ $type }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($rows->isEmpty())
                <div
                    class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-10 text-center text-gray-500 dark:text-gray-400">
                    {{ __('Belum ada peserta untuk jenjang yang Anda nilai pada lomba ini.') }}
                </div>
            @else
                <div
                    class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 dark:border-gray-700 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                <th class="px-4 py-3 whitespace-nowrap">{{ __('NPP') }}</th>
                                <th class="px-4 py-3 whitespace-nowrap">{{ __('Peserta') }}</th>
                                <th class="px-4 py-3 whitespace-nowrap">{{ __('Jenjang') }}</th>
                                @foreach ($criteria as $criterion)
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
                                <th class="px-4 py-3 text-right whitespace-nowrap">{{ __('Total') }}</th>
                                <th class="px-4 py-3 whitespace-nowrap">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($rows as $row)
                                <tr>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                        {{ $row->registration->npp }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                        {{ $maskIdentity ? __('Peserta :npp', ['npp' => $row->registration->npp]) : $row->registration->displayName() }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $typeColors[$row->registration->school_type] }}">
                                            {{ $row->registration->school_type }}
                                        </span>
                                    </td>
                                    @foreach ($criteria as $criterion)
                                        @php $score = $row->criteria_scores[$criterion->id]; @endphp
                                        @if (!$score['applicable'])
                                            <td class="px-4 py-3 text-right text-gray-300 dark:text-gray-600">&mdash;</td>
                                        @elseif (!$score['filled'])
                                            <td class="px-4 py-3 text-right text-amber-600 dark:text-amber-400">
                                                {{ __('Belum diisi') }}</td>
                                        @else
                                            <td class="px-4 py-3 text-right align-top text-gray-700 dark:text-gray-300">
                                                @if ($score['has_items'] && $score['sub_items']->isNotEmpty())
                                                    <div class="flex flex-col items-end">
                                                        <div class="space-y-1 text-xs text-gray-500 dark:text-gray-400 w-full min-w-[140px] pb-1.5 border-b border-gray-100 dark:border-gray-700/60 mb-1.5 print:hidden">
                                                            @foreach ($score['sub_items'] as $subItem)
                                                                <div class="flex items-center justify-between gap-3">
                                                                    <span class="truncate max-w-[120px] text-[11px]" title="{{ $subItem['name'] }}">{{ $subItem['name'] }}</span>
                                                                    <span class="font-medium text-gray-700 dark:text-gray-300 shrink-0 text-xs">{{ $subItem['score'] ?? '-' }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div class="flex items-center justify-end gap-1 font-semibold text-gray-900 dark:text-gray-100">
                                                            <span class="text-[10px] uppercase tracking-wider text-gray-400 font-normal print:hidden">{{ __('Total') }}:</span>
                                                            <span class="text-sm font-bold text-red-600 dark:text-red-400">{{ $score['score'] }}</span>
                                                            @if (filled($score['notes']))
                                                                <div class="relative inline-block" x-data="{ open: false }"
                                                                    @click.outside="open = false">
                                                                    <button type="button" @click="open = !open"
                                                                        class="ms-1 align-middle text-gray-400 hover:text-red-600 dark:hover:text-red-400"
                                                                        aria-label="{{ __('Lihat catatan') }}">
                                                                        <svg class="inline h-3.5 w-3.5" fill="none"
                                                                            viewBox="0 0 24 24" stroke="currentColor"
                                                                            stroke-width="2">
                                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                                        </svg>
                                                                    </button>
                                                                    <div x-show="open" x-transition
                                                                        class="absolute z-10 right-0 top-full mt-1 w-56 rounded-lg bg-gray-900 dark:bg-gray-700 text-white text-[11px] leading-snug p-2.5 shadow-lg normal-case font-normal text-left"
                                                                        style="display: none;">
                                                                        {{ $score['notes'] }}
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="flex items-center justify-end gap-1">
                                                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $score['score'] }}</span>
                                                        @if (filled($score['notes']))
                                                            <div class="relative inline-block" x-data="{ open: false }"
                                                                @click.outside="open = false">
                                                                <button type="button" @click="open = !open"
                                                                    class="ms-1 align-middle text-gray-400 hover:text-red-600 dark:hover:text-red-400"
                                                                    aria-label="{{ __('Lihat catatan') }}">
                                                                    <svg class="inline h-3.5 w-3.5" fill="none"
                                                                        viewBox="0 0 24 24" stroke="currentColor"
                                                                        stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                                    </svg>
                                                                </button>
                                                                <div x-show="open" x-transition
                                                                    class="absolute z-10 right-0 top-full mt-1 w-56 rounded-lg bg-gray-900 dark:bg-gray-700 text-white text-[11px] leading-snug p-2.5 shadow-lg normal-case font-normal text-left"
                                                                    style="display: none;">
                                                                    {{ $score['notes'] }}
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach
                                    <td class="px-4 py-3 text-right text-lg font-bold text-red-600 dark:text-red-400">
                                        {{ $row->total_score }}</td>
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
                                                title="{{ __(':filled dari :total kriteria dinilai', ['filled' => $row->filled_count, 'total' => $row->total_count]) }}">
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
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    @endif
</div>
