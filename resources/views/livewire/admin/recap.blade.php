@php
    $typeColors = [
        'SD' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
        'SMP' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300',
        'SMA' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',
        'Umum' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        'Marching Band' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
        'Drum Band' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
    ];

    $totalCount = $rows->count();
    $completedCount = $rows->where('status', 'complete')->count();
    $partialCount = $rows->where('status', 'partial')->count();
    $unassessedCount = $rows->where('status', 'unassessed')->count();
    $topScore = $rows->max('final_score') ?? 0;
    $completedPercent = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;
@endphp

<div class="space-y-6" x-data x-on:trigger-print.window="setTimeout(() => window.print(), 150)">
    @if (!$competition)
        <div
            class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-10 text-center text-gray-500 dark:text-gray-400">
            {{ __('Lomba tidak ditemukan.') }}
        </div>
    @else
        {{-- Header Card (Info Lomba & Aksi Cetak) --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700/80 rounded-2xl p-4 sm:p-6 print:hidden">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h2 class="text-xl sm:text-2xl font-black tracking-tight text-gray-900 dark:text-gray-100">
                            {{ $competition->name }}
                        </h2>
                        @if ($competition->isActive())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 ring-1 ring-emerald-200 dark:ring-emerald-800 shadow-2xs">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                {{ __('Berlangsung') }}
                            </span>
                        @elseif ($competition->isUpcoming())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 ring-1 ring-sky-200 dark:ring-sky-800">
                                <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                                {{ __('Terjadwal') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                {{ __('Selesai') }}
                            </span>
                        @endif
                        @if ($maskIdentity)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 text-xs font-semibold ring-1 ring-amber-200 dark:ring-amber-800">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                                {{ __('Identitas Disamarkan') }}
                            </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ $competition->start_time->translatedFormat('d M Y H:i') }} &ndash; {{ $competition->end_time->translatedFormat('d M Y H:i') }}
                        </span>
                        <span>&middot;</span>
                        <span class="inline-flex items-center gap-1">
                            <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-2.13a4 4 0 100-8 4 4 0 000 8zm6 1a4 4 0 00-3-3.87" />
                            </svg>
                            {{ __(':count Peserta terdaftar', ['count' => $totalAllParticipants]) }}
                        </span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-2">
                    <x-secondary-button type="button" onclick="window.print()" class="gap-1.5 text-xs py-2">
                        <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>{{ __('Cetak') }}</span>
                    </x-secondary-button>

                    <x-secondary-button type="button" wire:click="printAllTypes" class="gap-1.5 text-xs py-2" title="{{ __('Cetak seluruh jenjang sekaligus') }}">
                        <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                        </svg>
                        <span>{{ __('Cetak Semua Jenjang') }}</span>
                    </x-secondary-button>

                    @if ($school_type !== '')
                        <x-secondary-button type="button" wire:click="openBeritaAcara" class="gap-1.5 text-xs py-2 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800 bg-indigo-50/50 dark:bg-indigo-950/40 hover:bg-indigo-100">
                            <svg class="h-4 w-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>{{ __('Berita Acara') }}</span>
                        </x-secondary-button>
                    @endif

                    <x-primary-button type="button" wire:click="downloadPdf" wire:loading.attr="disabled" class="gap-1.5 text-xs py-2 shadow-2xs">
                        <svg wire:loading.remove wire:target="downloadPdf" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <svg wire:loading wire:target="downloadPdf" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="downloadPdf">{{ __('Unduh PDF') }}</span>
                        <span wire:loading wire:target="downloadPdf">{{ __('Menyiapkan...') }}</span>
                    </x-primary-button>
                </div>
            </div>
        </div>

        {{-- KPI Overview Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 print:hidden">
            {{-- Card 1: Total Peserta --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700/80 flex items-center gap-3.5">
                <div class="h-11 w-11 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-2.13a4 4 0 100-8 4 4 0 000 8zm6 1a4 4 0 00-3-3.87" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __('Peserta Terfilter') }}</p>
                    <p class="text-xl font-black text-gray-900 dark:text-gray-100 tabular-nums">{{ $totalCount }}</p>
                </div>
            </div>

            {{-- Card 2: Selesai Dinilai --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700/80 flex items-center gap-3.5">
                <div class="h-11 w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-baseline justify-between">
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __('Lengkap Dinilai') }}</p>
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ $completedPercent }}%</span>
                    </div>
                    <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 tabular-nums">{{ $completedCount }} <span class="text-xs font-normal text-gray-400">/ {{ $totalCount }}</span></p>
                </div>
            </div>

            {{-- Card 3: Dalam Penilaian / Belum --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700/80 flex items-center gap-3.5">
                <div class="h-11 w-11 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __('Status Penilaian') }}</p>
                    <p class="text-xl font-black text-amber-600 dark:text-amber-400 tabular-nums">
                        {{ $partialCount }}
                        <span class="text-xs font-normal text-gray-400">sebagian &middot; {{ $unassessedCount }} belum</span>
                    </p>
                </div>
            </div>

            {{-- Card 4: Nilai Tertinggi --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700/80 flex items-center gap-3.5">
                <div class="h-11 w-11 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __('Skor Puncak Juara 1') }}</p>
                    <p class="text-xl font-black text-rose-600 dark:text-rose-400 tabular-nums">{{ $topScore }} <span class="text-xs font-normal text-gray-400">poin</span></p>
                </div>
            </div>
        </div>

        {{-- Print Only Header --}}
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

        {{-- Toolbar Filter & Pencarian --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-gray-800 p-3 sm:p-4 rounded-2xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700/80 print:hidden">
            {{-- Category Segmented Navigation --}}
            <div class="flex flex-wrap items-center gap-1.5 p-1 rounded-xl bg-gray-100 dark:bg-gray-900/60 ring-1 ring-gray-200/50 dark:ring-gray-800">
                <button type="button" wire:click="$set('school_type', '')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ $school_type === '' ? 'bg-red-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                    <span>{{ __('Semua') }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $school_type === '' ? 'bg-red-700 text-white' : 'bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                        {{ $totalAllParticipants }}
                    </span>
                </button>
                @foreach ($competition->categories_list as $type)
                    <button type="button" wire:click="$set('school_type', '{{ $type }}')"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ $school_type === $type ? 'bg-red-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                        <span>{{ $type }}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $school_type === $type ? 'bg-red-700 text-white' : 'bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                            {{ $categoryCounts[$type] ?? 0 }}
                        </span>
                    </button>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Quick Search --}}
                <div class="relative w-full sm:w-64">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari nama atau NPP...') }}" class="block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 pl-9 pr-8 text-xs focus:border-red-500 focus:ring-red-500 placeholder-gray-400">
                    @if ($search !== '')
                        <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 hover:text-gray-600">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- Include notes toggle --}}
                <label class="inline-flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400 cursor-pointer select-none">
                    <input type="checkbox" wire:model.live="include_notes"
                        class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-red-600 shadow-sm focus:ring-red-500">
                    <span>{{ __('Catatan saat cetak') }}</span>
                </label>
            </div>
        </div>

        @if ($rows->isEmpty())
            <div
                class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-12 text-center text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="text-base font-semibold text-gray-800 dark:text-gray-200">
                    {{ $search !== '' ? __('Tidak ada peserta yang cocok dengan pencarian.') : __('Belum ada peserta terdaftar untuk lomba ini.') }}
                </p>
                @if ($search !== '')
                    <button type="button" wire:click="$set('search', '')" class="mt-2 text-xs font-semibold text-red-600 hover:underline">
                        {{ __('Hapus filter pencarian') }}
                    </button>
                @endif
            </div>
        @else
            <div
                class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700/80 rounded-2xl overflow-x-auto print:overflow-visible print:shadow-none print:ring-0">
                <table class="min-w-[1050px] w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-200 dark:border-gray-700/80 bg-gray-50/90 dark:bg-gray-800/90 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <th class="px-4 py-3.5 text-center whitespace-nowrap w-24">{{ __('Peringkat') }}</th>
                            <th class="px-4 py-3.5 text-left min-w-[220px] max-w-[320px]">{{ __('Peserta') }}</th>
                            <th class="px-4 py-3.5 text-center whitespace-nowrap">{{ __('Jenjang') }}</th>
                            @foreach ($competition->criteria as $criterion)
                                <th class="px-4 py-3.5 text-right whitespace-nowrap min-w-[130px]"
                                    title="{{ $criterion->description }}">
                                    <div class="font-bold text-gray-700 dark:text-gray-300">{{ $criterion->name }}</div>
                                    @if ($criterion->hasItems())
                                        <span class="inline-block text-[10px] font-normal text-gray-400 dark:text-gray-500 lowercase print:hidden">
                                            ({{ __(':count sub-item', ['count' => $criterion->items->count()]) }})
                                        </span>
                                    @endif
                                </th>
                            @endforeach
                            <th class="px-4 py-3.5 text-right whitespace-nowrap" title="{{ __('Akumulasi nilai murni dari seluruh juri') }}">{{ __('Nilai Murni') }}</th>
                            <th class="px-3 py-3.5 text-center whitespace-nowrap" title="{{ __('Penambahan nilai bonus') }}">{{ __('(+)') }}</th>
                            <th class="px-3 py-3.5 text-center whitespace-nowrap" title="{{ __('Pengurangan nilai penalti/potongan') }}">{{ __('(-)') }}</th>
                            <th class="px-4 py-3.5 text-right whitespace-nowrap">{{ __('Nilai Akhir') }}</th>
                            <th class="px-4 py-3.5 text-center whitespace-nowrap">{{ __('Status') }}</th>
                            <th class="px-4 py-3.5 text-center whitespace-nowrap print:hidden">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                        @foreach ($rows as $row)
                            <tr class="hover:bg-red-50/20 dark:hover:bg-red-950/10 transition-colors">
                                {{-- Peringkat --}}
                                <td class="px-4 py-3.5 text-center align-middle">
                                    <div class="flex flex-col items-center justify-center">
                                        @if ($loop->iteration === 1)
                                            <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-gradient-to-br from-amber-300 to-amber-500 text-amber-950 font-black text-xs shadow-2xs ring-2 ring-amber-200 dark:ring-amber-800" title="Juara 1">
                                                1
                                            </span>
                                        @elseif ($loop->iteration === 2)
                                            <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-gradient-to-br from-slate-200 to-slate-400 text-slate-900 font-black text-xs shadow-2xs ring-2 ring-slate-200 dark:ring-slate-700" title="Juara 2">
                                                2
                                            </span>
                                        @elseif ($loop->iteration === 3)
                                            <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-gradient-to-br from-amber-600 to-amber-700 text-white font-black text-xs shadow-2xs ring-2 ring-amber-500/50" title="Juara 3">
                                                3
                                            </span>
                                        @elseif ($loop->iteration >= 4 && $loop->iteration <= 6)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 text-[10px] font-bold border border-sky-200 dark:border-sky-800 whitespace-nowrap">
                                                {{ __('Harapan :n', ['n' => $loop->iteration - 3]) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-bold text-xs">
                                                {{ $loop->iteration }}
                                            </span>
                                        @endif

                                        @if ($row->is_tied)
                                            <button type="button"
                                                wire:click="openTieOrder('{{ $row->registration->school_type }}', {{ (int) $row->total_score }})"
                                                class="mt-1 inline-flex items-center gap-1 text-[10px] font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 rounded px-1.5 py-0.5 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition print:hidden"
                                                title="{{ __('Nilai sama, klik untuk atur urutan peringkat') }}">
                                                <svg class="h-2.5 w-2.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                                </svg>
                                                {{ __('Urutan') }}
                                            </button>
                                        @endif
                                    </div>
                                </td>

                                {{-- Peserta --}}
                                <td class="px-4 py-3.5 align-middle min-w-[220px] max-w-[320px]">
                                    <div class="space-y-0.5">
                                        <p class="font-bold text-gray-900 dark:text-gray-100 text-sm leading-snug">
                                            {{ $maskIdentity ? __('Peserta :rank', ['rank' => $loop->iteration]) : $row->registration->displayName() }}
                                        </p>
                                        <div class="flex items-center gap-2">
                                            @if (! $maskIdentity)
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded font-mono text-[11px] font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                                    {{ $row->npp }}
                                                </span>
                                            @endif
                                            <button type="button"
                                                wire:click="viewJudgeDetail('{{ $row->registration->id }}')"
                                                class="inline-flex items-center gap-1 text-[11px] font-medium text-red-600 hover:text-red-700 dark:text-red-400 hover:underline print:hidden"
                                                title="{{ __('Lihat rincian nilai per juri') }}">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                {{ __('Detail Juri') }}
                                            </button>
                                        </div>
                                    </div>
                                </td>

                                {{-- Jenjang --}}
                                <td class="px-4 py-3.5 align-middle text-center whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $typeColors[$row->registration->school_type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                        {{ $row->registration->school_type }}
                                    </span>
                                </td>

                                {{-- Kriteria Scores --}}
                                @foreach ($competition->criteria as $criterion)
                                    @php $score = $row->criteria_scores[$criterion->id]; @endphp
                                    @if (!$score['applicable'])
                                        <td class="px-4 py-3.5 text-right text-gray-300 dark:text-gray-600 align-middle">&mdash;</td>
                                    @else
                                        <td
                                            class="px-4 py-3.5 text-right align-top {{ $score['expected'] > 0 && $score['submitted'] < $score['expected'] ? 'text-amber-600 dark:text-amber-400' : 'text-gray-700 dark:text-gray-300' }}">
                                            @if (!empty($score['has_items']) && !empty($score['sub_items']) && $score['sub_items']->isNotEmpty())
                                                <div class="flex flex-col items-end">
                                                    <div class="space-y-1 text-xs text-gray-500 dark:text-gray-400 w-full min-w-[140px] pb-1.5 border-b border-gray-100 dark:border-gray-700/60 mb-1.5 print:hidden">
                                                        @foreach ($score['sub_items'] as $subItem)
                                                            <div class="flex items-center justify-between gap-3">
                                                                <span class="truncate max-w-[120px] text-[11px]" title="{{ $subItem['name'] }}">{{ $subItem['name'] }}</span>
                                                                <span class="font-medium text-gray-700 dark:text-gray-300 shrink-0 text-xs tabular-nums">{{ $subItem['score'] }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="flex items-center justify-end gap-1.5 font-bold text-gray-900 dark:text-gray-100">
                                                        <span class="text-[10px] uppercase tracking-wider text-gray-400 font-normal print:hidden">{{ __('Total') }}:</span>
                                                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100 tabular-nums">{{ $score['score'] }}</span>
                                                        @if ($score['expected'] > 0 && $score['submitted'] < $score['expected'])
                                                            <span class="text-[10px] px-1 py-0.2 rounded font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400"
                                                                title="{{ __(':submitted/:expected juri menilai', ['submitted' => $score['submitted'], 'expected' => $score['expected']]) }}">
                                                                {{ $score['submitted'] }}/{{ $score['expected'] }}
                                                            </span>
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
                                                                    class="absolute z-20 right-0 top-full mt-1 w-64 rounded-xl bg-gray-900 dark:bg-gray-800 text-white text-[11px] leading-snug p-3 shadow-xl ring-1 ring-white/10 normal-case font-normal text-left"
                                                                    style="display: none;">
                                                                    @foreach ($score['notes'] as $note)
                                                                        <div class="mb-2 last:mb-0 border-b border-gray-800 last:border-b-0 pb-1.5 last:pb-0">
                                                                            <span class="font-bold text-red-400">{{ $note['judge'] }}:</span>
                                                                            <p class="text-gray-200 mt-0.5">{{ $note['text'] }}</p>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <div class="flex items-center justify-end gap-1.5 font-bold">
                                                    <span class="tabular-nums text-sm">{{ $score['score'] }}</span>
                                                    @if ($score['expected'] > 0 && $score['submitted'] < $score['expected'])
                                                        <span class="text-[10px] px-1 py-0.2 rounded font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400"
                                                            title="{{ __(':submitted/:expected juri menilai', ['submitted' => $score['submitted'], 'expected' => $score['expected']]) }}">
                                                            {{ $score['submitted'] }}/{{ $score['expected'] }}
                                                        </span>
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
                                                                class="absolute z-20 right-0 top-full mt-1 w-64 rounded-xl bg-gray-900 dark:bg-gray-800 text-white text-[11px] leading-snug p-3 shadow-xl ring-1 ring-white/10 normal-case font-normal text-left"
                                                                style="display: none;">
                                                                @foreach ($score['notes'] as $note)
                                                                    <div class="mb-2 last:mb-0 border-b border-gray-800 last:border-b-0 pb-1.5 last:pb-0">
                                                                        <span class="font-bold text-red-400">{{ $note['judge'] }}:</span>
                                                                        <p class="text-gray-200 mt-0.5">{{ $note['text'] }}</p>
                                                                    </div>
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

                                {{-- Nilai Murni --}}
                                <td class="px-4 py-3.5 text-right font-medium text-gray-700 dark:text-gray-300 align-middle tabular-nums">
                                    {{ $row->raw_score }}
                                </td>

                                {{-- Bonus (+) --}}
                                <td class="px-3 py-3.5 text-center align-middle whitespace-nowrap">
                                    @if ($row->score_addition > 0)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 ring-1 ring-emerald-200 dark:ring-emerald-800"
                                            title="{{ $row->adjustment_notes ?: __('Penambahan nilai') }}">
                                            +{{ $row->score_addition }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">&ndash;</span>
                                    @endif
                                </td>

                                {{-- Potongan (-) --}}
                                <td class="px-3 py-3.5 text-center align-middle whitespace-nowrap">
                                    @if ($row->score_deduction > 0)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 ring-1 ring-rose-200 dark:ring-rose-800"
                                            title="{{ $row->adjustment_notes ?: __('Pengurangan nilai') }}">
                                            -{{ $row->score_deduction }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">&ndash;</span>
                                    @endif
                                </td>

                                {{-- Nilai Akhir --}}
                                <td class="px-4 py-3.5 text-right align-middle whitespace-nowrap">
                                    <div class="text-right">
                                        <span class="text-base font-black text-red-600 dark:text-red-400 tabular-nums">
                                            {{ $row->final_score }}
                                        </span>
                                        @if (filled($row->adjustment_notes))
                                            <p class="text-[10px] font-normal text-gray-400 dark:text-gray-500 truncate max-w-[8rem] ms-auto"
                                                title="{{ $row->adjustment_notes }}">
                                                {{ $row->adjustment_notes }}
                                            </p>
                                        @endif
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3.5 text-center align-middle whitespace-nowrap">
                                    @if ($row->status === 'complete')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 ring-1 ring-emerald-200 dark:ring-emerald-800">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            {{ __('Lengkap') }}
                                        </span>
                                    @elseif ($row->status === 'partial')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-800"
                                            title="{{ __(':submitted dari :expected penilaian juri masuk', ['submitted' => $row->submitted_count, 'expected' => $row->expected_count]) }}">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            {{ __('Belum lengkap') }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                                            {{ __('Belum dinilai') }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-4 py-3.5 text-center align-middle whitespace-nowrap print:hidden">
                                    <button type="button"
                                        wire:click="openAdjustment('{{ $row->registration->id }}')"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-2xs hover:border-red-300 transition"
                                        title="{{ __('Sesuaikan nilai tambah/kurang') }}">
                                        <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                        </svg>
                                        <span>{{ __('Sesuaikan') }}</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="text-xs text-gray-400 dark:text-gray-500 print:hidden">
                {{ __('* Skor kriteria bertanda kuning/jumlah juri menandakan belum dinilai oleh seluruh juri yang ditugaskan.') }}
                @if (!$include_notes)
                    <br>{{ __('Klik ikon catatan pada nilai untuk melihat catatan juri, atau centang "Catatan saat cetak" untuk menampilkannya langsung.') }}
                @endif
            </p>
        @endif

        <p class="hidden print:block mt-6 pt-3 border-t border-gray-300 text-center text-[10px] text-gray-400">
            {{ __('Bidang Humas dan Informatika Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan') }} &middot;
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
                            class="shrink-0 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $typeColors[$judgeDetail->registration->school_type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
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
