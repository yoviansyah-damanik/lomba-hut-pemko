<div class="space-y-6">
    {{-- Quick Action Bar --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4 sm:p-5">
        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
            {{ __('Pintasan Aksi Cepat') }}
        </h3>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('evaluation') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-red-600 text-white hover:bg-red-700 shadow-sm shadow-red-500/20 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                {{ __('Mulai Penilaian Peserta') }}
            </a>
            <a href="{{ route('evaluation.recap') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                {{ __('Rekap Nilai Saya') }}
            </a>
        </div>
    </div>

    {{-- Task Remaining Alert --}}
    @if ($totalAssigned > 0 && $remainingCount > 0)
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-sm">
            <div class="flex items-center gap-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-800/60 text-amber-700 dark:text-amber-300 font-bold text-xs">
                    {{ $remainingCount }}
                </span>
                <div>
                    <p class="font-medium">
                        {{ __('Tersisa :count penilaian peserta yang perlu Anda selesaikan.', ['count' => $remainingCount]) }}
                    </p>
                    <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">
                        {{ __('Segera masukkan nilai agar rekapitulasi penilaian dan live rank tetap mutakhir.') }}
                    </p>
                </div>
            </div>
            <a href="{{ route('evaluation') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-600 text-white hover:bg-amber-700 transition shrink-0 self-start sm:self-auto">
                {{ __('Buka Lembar Penilaian') }} &rarr;
            </a>
        </div>
    @elseif ($totalAssigned > 0 && $remainingCount === 0)
        <div class="flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 text-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="font-semibold">{{ __('Semua Penilaian Selesai!') }}</p>
                <p class="text-xs text-emerald-700 dark:text-emerald-300">{{ __('Seluruh peserta pada kriteria yang ditugaskan telah berhasil Anda nilai. Terima kasih atas dedikasi Anda.') }}</p>
            </div>
        </div>
    @endif

    {{-- Active Competitions Spotlight --}}
    @if ($activeCompetitions->isNotEmpty())
        <div>
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                {{ __('Lomba Aktif Ditugaskan') }}
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($activeCompetitions as $activeComp)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                    {{ __('Sedang Berlangsung') }}
                                </span>
                            </div>
                            <h4 class="font-semibold text-sm text-gray-900 dark:text-gray-100 line-clamp-1">
                                {{ $activeComp->name }}
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-1">
                                {{ $activeComp->location ?? __('Lokasi belum ditentukan') }}
                            </p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end">
                            <a href="{{ route('evaluation') }}"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 dark:text-red-400 hover:text-red-700 transition">
                                {{ __('Nilai Peserta') }} &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Hero stat -->
    @php
        $percentage = $totalAssigned > 0 ? min(100, round(($totalCompleted / $totalAssigned) * 100)) : 0;
    @endphp
    <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4 sm:p-5">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total Penilaian Selesai') }}</p>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $percentage === 100 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                {{ $percentage }}%
            </span>
        </div>
        <p class="mt-1 text-3xl sm:text-4xl font-bold text-gray-900 dark:text-gray-100">
            {{ $totalCompleted }} <span class="text-base sm:text-lg font-medium text-gray-400">/ {{ $totalAssigned }}</span>
        </p>
        <div class="mt-3 h-2 w-full rounded-full bg-red-100 dark:bg-red-900/30 overflow-hidden">
            <div class="h-full rounded-full bg-red-500 transition-all duration-500" style="width: {{ $percentage }}%"></div>
        </div>
        <div class="mt-2.5 flex items-center justify-between text-xs text-gray-400 dark:text-gray-500">
            <span>{{ __('Tersisa :count dari total :total peserta', ['count' => $remainingCount, 'total' => $totalAssigned]) }}</span>
            <span>{{ __(':completed dinilai', ['completed' => $totalCompleted]) }}</span>
        </div>
    </div>

    <!-- Trend -->
    <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4 sm:p-5">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('Aktivitas Penilaian Anda (7 Hari Terakhir)') }}</h3>
        <x-charts.trend-bar :data="$trend" />
    </div>

    <!-- Riwayat -->
    <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4 sm:p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __('Riwayat Penilaian Terbaru') }}</h3>
            <a href="{{ route('evaluation.recap') }}"
                class="text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                {{ __('Lihat Rekap Lengkap') }} &rarr;
            </a>
        </div>
        @if ($recentEvaluations->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Anda belum memiliki riwayat penilaian.') }}</p>
        @else
            <div class="space-y-2">
                @foreach ($recentEvaluations as $evaluation)
                    <div class="flex items-center justify-between gap-3 py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                {{ $evaluation->registration?->displayName() }}
                                <span class="text-xs font-normal text-gray-400">({{ $evaluation->registration?->npp }})</span>
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 truncate">
                                {{ $evaluation->criterion?->name }} &middot; {{ $evaluation->criterion?->competition?->name }}
                                &middot; {{ $evaluation->updated_at->diffForHumans() }}
                            </p>
                        </div>
                        <span class="shrink-0 text-lg font-bold text-red-600 dark:text-red-400">{{ $evaluation->score }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Progress per kriteria -->
    @php
        $typeColors = [
            'SD' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
            'SMP' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300',
            'SMA' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',
            'Umum' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
            'Marching Band' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
            'Drum Band' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
        ];
    @endphp
    <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4 sm:p-5">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('Progres per Kriteria') }}</h3>
        @if ($criteria->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Anda belum memiliki kriteria yang ditugaskan.') }}</p>
        @else
            <div class="space-y-4">
                @foreach ($criteria as $criterion)
                    <div>
                        <x-charts.meter :value="$criterion->evaluations_count" :max="$criterion->registrations_count">
                            <x-slot:label>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ $typeColors[$criterion->assignment_school_type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                    {{ $criterion->assignment_school_type }}
                                </span>
                                {{ $criterion->name }}
                            </x-slot:label>
                        </x-charts.meter>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                            {{ $criterion->competition->name }}
                            @if ($criterion->competition->isActive())
                                <span class="ms-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">{{ __('Berlangsung') }}</span>
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
