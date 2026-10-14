<div class="space-y-6">
    {{-- Quick Actions --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4 sm:p-5">
        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
            {{ __('Pintasan Aksi Cepat') }}
        </h3>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.competitions') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-medium bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/50 transition">
                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                {{ __('Kelola Lomba') }}
            </a>
            <a href="{{ route('admin.participants') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-2.13a4 4 0 100-8 4 4 0 000 8zm6 1a4 4 0 00-3-3.87" />
                </svg>
                {{ __('Kelola Peserta') }}
            </a>
            <a href="{{ route('admin.judges') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ __('Kelola Juri') }}
            </a>
            @if ($latestCompetition)
                <a href="{{ route('admin.recap', $latestCompetition->id) }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ __('Rekap Nilai (:name)', ['name' => Str::limit($latestCompetition->name, 16)]) }}
                </a>
                <a href="{{ route('live-rank.show', $latestCompetition->id) }}" target="_blank"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-medium bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    {{ __('Live Rank Publik') }} &nearr;
                </a>
            @endif
        </div>
    </div>

    {{-- Actionable Alerts --}}
    @if ($unassignedCriteriaCount > 0)
        <div class="flex items-center justify-between p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-sm">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ __('Terdapat :count kriteria perlombaan yang belum memiliki dewan juri ditugaskan.', ['count' => $unassignedCriteriaCount]) }}</span>
            </div>
            <a href="{{ route('admin.competitions') }}" class="font-semibold underline hover:no-underline text-xs shrink-0">
                {{ __('Atur Juri') }} &rarr;
            </a>
        </div>
    @endif

    {{-- Active Competitions Spotlight --}}
    @if ($activeCompetitions->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4 sm:p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-green-500 animate-pulse"></span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase tracking-wide">
                        {{ __('Lomba Sedang Berlangsung Saat Ini') }} ({{ $activeCompetitions->count() }})
                    </h3>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach ($activeCompetitions as $activeComp)
                    <div class="p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 dark:text-gray-100 truncate text-sm">{{ $activeComp->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $activeComp->start_time->translatedFormat('H:i') }} &ndash; {{ $activeComp->end_time->translatedFormat('H:i') }}
                                &middot; {{ $activeComp->registrations()->count() }} {{ __('peserta') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <a href="{{ route('admin.recap', $activeComp->id) }}"
                                class="px-2.5 py-1 text-xs font-medium rounded-lg bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                {{ __('Rekap') }}
                            </a>
                            <a href="{{ route('live-rank.show', $activeComp->id) }}" target="_blank"
                                class="px-2.5 py-1 text-xs font-medium rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                                {{ __('Live Rank') }} &nearr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 4 Stat Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4">
            <div class="flex items-center justify-center h-9 w-9 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-300 mb-3">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-2.13a4 4 0 100-8 4 4 0 000 8zm6 1a4 4 0 00-3-3.87" />
                </svg>
            </div>
            <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $judgeCount }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Juri') }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4">
            <div class="flex items-center justify-center h-9 w-9 rounded-full bg-violet-100 dark:bg-violet-900/40 text-violet-600 dark:text-violet-300 mb-3">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $criterionCount }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Kriteria') }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4">
            <div class="flex items-center justify-center h-9 w-9 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300 mb-3">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.42A9.99 9.99 0 0121 12c0 2.21-1.79 4-4 4H7c-2.21 0-4-1.79-4-4 0-1.34.53-2.6 1.84-3.42L12 14zm0 0v7" />
                </svg>
            </div>
            <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $participantsByType->sum() }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Peserta') }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                @foreach (['SD', 'SMP', 'SMA', 'Umum'] as $type)
                    {{ $type }}: {{ $participantsByType[$type] ?? 0 }}@if (! $loop->last), @endif
                @endforeach
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4">
            <div class="flex items-center justify-center h-9 w-9 rounded-full bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-300 mb-3">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z" />
                </svg>
            </div>
            <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $evaluationCount }} <span class="text-sm sm:text-base font-medium text-gray-400">/ {{ $expectedEvaluations }}</span></p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Progres Penilaian') }}</p>
        </div>
    </div>

    <!-- Trend -->
    <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4 sm:p-5">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('Aktivitas Penilaian (7 Hari Terakhir)') }}</h3>
        <x-charts.trend-bar :data="$trend" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Progress per lomba -->
        <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4 sm:p-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('Progres per Lomba') }}</h3>
            @if ($competitionProgress->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Belum ada lomba dengan kriteria.') }}</p>
            @else
                <div class="space-y-4">
                    @foreach ($competitionProgress as $row)
                        <x-charts.meter :label="$row['label']" :value="$row['value']" :max="$row['max']" />
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Distribusi peserta -->
        <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4 sm:p-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('Distribusi Peserta per Jenis Sekolah') }}</h3>
            @php
                $typeColors = ['SD' => 'bg-sky-500', 'SMP' => 'bg-violet-500', 'SMA' => 'bg-teal-500', 'Umum' => 'bg-amber-500'];
                $typeTotal = max(1, $participantsByType->sum());
            @endphp
            <div class="flex h-3 w-full rounded-full overflow-hidden gap-0.5 bg-gray-100 dark:bg-gray-700">
                @foreach (['SD', 'SMP', 'SMA', 'Umum'] as $type)
                    @php $count = $participantsByType[$type] ?? 0; @endphp
                    @if ($count > 0)
                        <div class="{{ $typeColors[$type] }}" style="width: {{ ($count / $typeTotal) * 100 }}%"></div>
                    @endif
                @endforeach
            </div>
            <div class="mt-4 space-y-2">
                @foreach (['SD', 'SMP', 'SMA', 'Umum'] as $type)
                    @php $count = $participantsByType[$type] ?? 0; @endphp
                    <div class="flex items-center justify-between text-sm">
                        <span class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                            <span class="h-2.5 w-2.5 rounded-full {{ $typeColors[$type] }} shrink-0"></span>
                            {{ $type }}
                        </span>
                        <span class="text-gray-500 dark:text-gray-400 tabular-nums">{{ $count }} ({{ $typeTotal > 0 ? round(($count / $typeTotal) * 100) : 0 }}%)</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Progress per judge -->
    <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4 sm:p-5">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('Progres per Juri') }}</h3>
        @if ($judgeProgress->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Belum ada juri dengan kriteria ditugaskan.') }}</p>
        @else
            <div class="space-y-4">
                @foreach ($judgeProgress as $row)
                    <x-charts.meter :label="$row['label']" :value="$row['value']" :max="$row['max']" />
                @endforeach
            </div>
        @endif
    </div>
</div>
