@php
    $typeColors = [
        'SD' => 'bg-sky-100 text-sky-800 border-sky-200 dark:bg-sky-950/60 dark:text-sky-300 dark:border-sky-800',
        'SMP' => 'bg-violet-100 text-violet-800 border-violet-200 dark:bg-violet-950/60 dark:text-violet-300 dark:border-violet-800',
        'SMA' => 'bg-teal-100 text-teal-800 border-teal-200 dark:bg-teal-950/60 dark:text-teal-300 dark:border-teal-800',
        'Umum' => 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
        'Marching Band' => 'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
        'Drum Band' => 'bg-indigo-100 text-indigo-800 border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800',
    ];
    $maxScore = $participants->max('total_score') ?: 1;
    $podium = $participants->take(3);
    $rest = $participants->slice(3);
@endphp

<div wire:poll.5s class="space-y-6 sm:space-y-8">
    @if (!$competition)
        <div class="backdrop-blur-md bg-white/80 dark:bg-slate-800/80 shadow-xl ring-1 ring-slate-200 dark:ring-slate-700 rounded-3xl p-12 text-center">
            <div class="h-16 w-16 mx-auto rounded-2xl bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 flex items-center justify-center text-3xl mb-4">
                🔒
            </div>
            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ __('Live Rank Belum Dibuka') }}</h3>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                {{ __('Papan peringkat resmi untuk lomba ini sedang dinonaktifkan oleh panitia atau belum dimulai.') }}
            </p>
        </div>
    @else
        {{-- Hero Stage Header --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-red-600 via-rose-600 to-red-800 text-white shadow-2xl p-6 sm:p-8 lg:p-10">
            {{-- Radial shine decoration --}}
            <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-amber-400/10 blur-2xl pointer-events-none"></div>

            <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs font-bold tracking-wider uppercase mb-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/25 backdrop-blur-md border border-white/20 text-white shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                            <span class="h-2 w-2 -ml-3.5 rounded-full bg-emerald-400"></span>
                            <span>{{ __('Live Scoreboard') }}</span>
                        </span>

                        @if ($competition->isActive())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/30 backdrop-blur-md border border-emerald-300/40 text-emerald-100">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                                {{ __('Lomba Sedang Berlangsung') }}
                            </span>
                        @endif

                        @if ($maskIdentity)
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-400/25 backdrop-blur-md border border-amber-300/40 text-amber-200">
                                🕵️ {{ __('Identitas Disamarkan') }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-white drop-shadow-sm">
                        {{ $competition->name }}
                    </h1>

                    <div class="mt-3 flex flex-wrap items-center gap-y-1 gap-x-4 text-xs sm:text-sm text-red-100">
                        @if ($competition->location)
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-red-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ $competition->location }}
                            </span>
                            <span>&bull;</span>
                        @endif
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-red-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ __('Sinkronisasi Realtime :time', ['time' => now()->format('H:i:s')]) }}
                        </span>
                    </div>
                </div>

                {{-- Metric Pill on Banner --}}
                <div class="flex items-center gap-3 shrink-0">
                    <div class="backdrop-blur-md bg-black/20 border border-white/15 rounded-2xl px-4 py-3 flex items-center gap-3 shadow-lg">
                        <div class="h-10 w-10 rounded-xl bg-amber-400/20 border border-amber-300/30 flex items-center justify-center text-xl">
                            🏆
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-red-200 uppercase tracking-wider">{{ __('Format Tayangan') }}</p>
                            <p class="text-sm font-black text-white">{{ __('6 Terbaik Resmi') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Category Segmented Navigation --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base sm:text-lg font-black tracking-tight text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <span>{{ __('Papan Klasemen') }}</span>
                    <span class="px-2 py-0.5 rounded-lg text-xs font-bold {{ $typeColors[$school_type] ?? 'bg-slate-200 text-slate-700' }}">
                        {{ $school_type }}
                    </span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ __('Menampilkan Juara I - III & Harapan I - III dengan pembaruan otomatis') }}
                </p>
            </div>

            <div class="inline-flex p-1.5 rounded-2xl bg-slate-200/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-300/60 dark:border-slate-700/60 shadow-inner overflow-x-auto">
                @foreach ($categories as $type)
                    @php
                        $count = $schoolTypeCounts[$type] ?? 0;
                        $isActive = $school_type === $type;
                    @endphp
                    <button type="button" wire:click="$set('school_type', '{{ $type }}')"
                        class="relative px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all duration-200 flex items-center gap-1.5 whitespace-nowrap {{ $isActive ? 'bg-white dark:bg-slate-900 text-red-600 dark:text-red-400 shadow-md scale-[1.02]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-white/40 dark:hover:bg-slate-700/40' }}">
                        <span>{{ $type }}</span>
                        @if ($count > 0)
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold {{ $isActive ? 'bg-red-100 text-red-700 dark:bg-red-950/80 dark:text-red-300' : 'bg-slate-300 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                                {{ $count }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        @if ($participants->isEmpty())
            {{-- Empty State Card --}}
            <div class="backdrop-blur-md bg-white/70 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700/80 rounded-3xl p-12 text-center shadow-lg">
                <div class="h-20 w-20 mx-auto rounded-3xl bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-4xl mb-4 shadow-sm animate-float-gentle">
                    🎪
                </div>
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100">
                    {{ __('Belum Ada Nilai Masuk') }}
                </h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                    {{ __('Dewan juri belum memasukkan penilaian untuk peserta pada kategori :type.', ['type' => $school_type]) }}
                </p>
                <div class="mt-6 flex justify-center gap-2">
                    @foreach ($categories as $type)
                        @if ($type !== $school_type && ($schoolTypeCounts[$type] ?? 0) > 0)
                            <button type="button" wire:click="$set('school_type', '{{ $type }}')"
                                class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 transition">
                                {{ __('Lihat Kategori :type', ['type' => $type]) }} &rarr;
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>
        @else
            {{-- THE PODIUM (Juara 1, 2, 3) --}}
            <div class="pt-6 sm:pt-10 pb-4">
                <div class="grid grid-cols-3 items-end gap-2 sm:gap-4 md:gap-6 max-w-4xl mx-auto">
                    @php
                        // Ordering on podium: Juara 2 (Index 1) | Juara 1 (Index 0) | Juara 3 (Index 2)
                        $podiumOrder = [
                            1 => ['slot' => 2, 'data' => $podium->get(1)],
                            0 => ['slot' => 1, 'data' => $podium->get(0)],
                            2 => ['slot' => 3, 'data' => $podium->get(2)],
                        ];
                    @endphp

                    @foreach ($podiumOrder as $key => $col)
                        @php
                            $rank = $col['slot'];
                            $p = $col['data'];
                        @endphp

                        <div class="flex flex-col items-center justify-end w-full">
                            @if ($p)
                                {{-- Floater Trophy / Medal for Top 3 --}}
                                @if ($rank === 1)
                                    <div class="animate-float-gentle text-4xl sm:text-5xl lg:text-6xl mb-1 filter drop-shadow-[0_8px_12px_rgba(234,179,8,0.5)]">
                                        👑
                                    </div>
                                @elseif ($rank === 2)
                                    <div class="text-3xl sm:text-4xl lg:text-5xl mb-1 filter drop-shadow-md">
                                        🥈
                                    </div>
                                @else
                                    <div class="text-3xl sm:text-4xl lg:text-5xl mb-1 filter drop-shadow-md">
                                        🥉
                                    </div>
                                @endif

                                {{-- Avatar Card --}}
                                <div class="w-full flex flex-col items-center text-center px-1 sm:px-3 mb-2">
                                    {{-- Circular Avatar with Metallic Ring --}}
                                    <div class="relative mb-2">
                                        <div class="flex items-center justify-center font-black rounded-full shadow-xl transition-transform duration-300 hover:scale-105
                                            {{ $rank === 1 ? 'h-16 w-16 sm:h-24 sm:w-24 text-xl sm:text-3xl ring-4 sm:ring-8 ring-amber-400 animate-glow-gold bg-gradient-to-br from-amber-200 via-yellow-300 to-amber-500 text-amber-950' : '' }}
                                            {{ $rank === 2 ? 'h-14 w-14 sm:h-20 sm:w-20 text-lg sm:text-2xl ring-4 sm:ring-8 ring-slate-300 dark:ring-slate-500 animate-glow-silver bg-gradient-to-br from-slate-100 via-slate-200 to-slate-400 text-slate-800' : '' }}
                                            {{ $rank === 3 ? 'h-12 w-12 sm:h-18 sm:w-18 text-base sm:text-xl ring-4 sm:ring-8 ring-amber-600 dark:ring-amber-700 animate-glow-bronze bg-gradient-to-br from-amber-100 via-orange-200 to-amber-600 text-amber-950' : '' }}">
                                            @if ($maskIdentity)
                                                <span>{{ $rank }}</span>
                                            @else
                                                <span>{{ Str::of($p->displayName())->explode(' ')->reject(fn($w) => is_numeric($w))->map(fn($w) => Str::substr($w, 0, 1))->take(2)->implode('') }}</span>
                                            @endif
                                        </div>

                                        {{-- Rank Ribbon Badge --}}
                                        <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 px-2 py-0.5 rounded-full text-[9px] sm:text-[11px] font-black uppercase tracking-wider shadow-md whitespace-nowrap
                                            {{ $rank === 1 ? 'bg-gradient-to-r from-amber-500 to-yellow-500 text-amber-950 ring-2 ring-white dark:ring-slate-900' : '' }}
                                            {{ $rank === 2 ? 'bg-gradient-to-r from-slate-300 to-slate-400 text-slate-900 ring-2 ring-white dark:ring-slate-900' : '' }}
                                            {{ $rank === 3 ? 'bg-gradient-to-r from-amber-600 to-orange-600 text-white ring-2 ring-white dark:ring-slate-900' : '' }}">
                                            Juara {{ $rank }}
                                        </div>
                                    </div>

                                    {{-- Participant Name --}}
                                    <h4 class="mt-2 font-black text-xs sm:text-sm lg:text-base text-slate-900 dark:text-slate-100 line-clamp-1 sm:line-clamp-2 max-w-full">
                                        {{ $maskIdentity ? __('Peserta :rank', ['rank' => $rank]) : $p->displayName() }}
                                    </h4>

                                    @if (!$maskIdentity)
                                        <p class="text-[10px] sm:text-xs font-mono font-semibold text-slate-400 dark:text-slate-500 truncate max-w-full">
                                            {{ $p->npp }}
                                        </p>
                                    @endif

                                    {{-- Score Readout --}}
                                    <div class="mt-1 flex items-baseline justify-center gap-1">
                                        <span class="font-black tracking-tight drop-shadow-sm
                                            {{ $rank === 1 ? 'text-2xl sm:text-4xl lg:text-5xl text-amber-500 dark:text-yellow-400' : '' }}
                                            {{ $rank === 2 ? 'text-xl sm:text-3xl lg:text-4xl text-slate-700 dark:text-slate-200' : '' }}
                                            {{ $rank === 3 ? 'text-xl sm:text-3xl lg:text-4xl text-amber-700 dark:text-amber-400' : '' }}">
                                            {{ $p->total_score ?? 0 }}
                                        </span>
                                        <span class="text-[10px] sm:text-xs font-bold text-slate-400 dark:text-slate-500 uppercase">
                                            {{ __('Poin') }}
                                        </span>
                                    </div>

                                    {{-- Score Status Badge --}}
                                    <div class="mt-1">
                                        @if ($p->is_complete)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ __('Final') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 animate-pulse"
                                                title="{{ __('Nilai masih berjalan') }}">
                                                {{ __('Sementara') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- 3D Pedestal Pillar Base --}}
                                <div class="w-full rounded-t-3xl shadow-2xl relative overflow-hidden flex flex-col items-center justify-between transition-all duration-500 border-t-2
                                    {{ $rank === 1 ? 'h-36 sm:h-52 bg-gradient-to-b from-yellow-300 via-amber-400 to-yellow-600 dark:from-yellow-500 dark:via-amber-600 dark:to-yellow-800 border-yellow-200' : '' }}
                                    {{ $rank === 2 ? 'h-24 sm:h-36 bg-gradient-to-b from-slate-200 via-slate-300 to-slate-400 dark:from-slate-600 dark:via-slate-700 dark:to-slate-800 border-slate-100' : '' }}
                                    {{ $rank === 3 ? 'h-16 sm:h-28 bg-gradient-to-b from-amber-500 via-amber-600 to-amber-700 dark:from-amber-700 dark:via-amber-800 dark:to-amber-950 border-amber-400' : '' }}">

                                    {{-- Light reflection highlight at the top --}}
                                    <div class="w-full h-2 bg-white/40"></div>

                                    <div class="my-auto flex flex-col items-center drop-shadow-md">
                                        <span class="text-white font-black text-2xl sm:text-5xl lg:text-6xl drop-shadow-md">
                                            {{ $rank }}
                                        </span>
                                        <span class="text-white/90 font-extrabold text-[9px] sm:text-xs uppercase tracking-widest -mt-1 sm:mt-0 drop-shadow">
                                            JUARA {{ match($rank) { 1 => 'I', 2 => 'II', 3 => 'III' } }}
                                        </span>
                                    </div>

                                    <div class="w-full h-1 bg-black/10"></div>
                                </div>
                            @else
                                {{-- Empty slot on podium if not enough participants --}}
                                <div class="w-full h-32 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700 flex flex-col items-center justify-center p-3 text-slate-400">
                                    <span class="text-2xl">⏳</span>
                                    <span class="text-xs font-bold mt-1">Juara {{ $rank }}</span>
                                    <span class="text-[10px]">{{ __('Belum Terisi') }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- HARAPAN SECTION (Harapan 1 - 3) --}}
            @if ($rest->isNotEmpty())
                <div class="space-y-4 pt-4">
                    <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="flex h-7 w-7 rounded-xl bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 items-center justify-center font-bold text-sm">
                                🎖️
                            </span>
                            <h3 class="text-sm sm:text-base font-black tracking-tight text-slate-800 dark:text-slate-100">
                                {{ __('Peringkat Harapan (Harapan 1 – 3)') }}
                            </h3>
                        </div>
                        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">
                            {{ __(':count Peserta Terpilih', ['count' => $rest->count()]) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
                        @foreach ($rest as $participant)
                            @php
                                $rank = $loop->iteration + 3;
                                $harapanIndex = $rank - 3;
                                $harapanLabel = __('Harapan :n', ['n' => $harapanIndex]);

                                $theme = match ($harapanIndex) {
                                    1 => [
                                        'badge' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800',
                                        'bar' => 'from-rose-500 to-red-600',
                                        'score' => 'text-rose-600 dark:text-rose-400',
                                        'ring' => 'hover:ring-rose-400/50',
                                    ],
                                    2 => [
                                        'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800',
                                        'bar' => 'from-indigo-500 to-violet-600',
                                        'score' => 'text-indigo-600 dark:text-indigo-400',
                                        'ring' => 'hover:ring-indigo-400/50',
                                    ],
                                    3 => [
                                        'badge' => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/50 dark:text-teal-300 dark:border-teal-800',
                                        'bar' => 'from-teal-500 to-emerald-600',
                                        'score' => 'text-teal-600 dark:text-teal-400',
                                        'ring' => 'hover:ring-teal-400/50',
                                    ],
                                };

                                $percentage = max(10, min(100, round((($participant->total_score ?? 0) / $maxScore) * 100)));
                            @endphp

                            <div class="backdrop-blur-md bg-white/80 dark:bg-slate-800/80 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-700/80 shadow-md hover:shadow-xl transition-all duration-300 hover:scale-[1.01] flex flex-col justify-between {{ $theme['ring'] }}">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-3">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-black uppercase tracking-wider border {{ $theme['badge'] }}">
                                            <span>🎖️</span>
                                            <span>{{ $harapanLabel }}</span>
                                        </span>

                                        @if ($participant->is_complete)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ __('Final') }}
                                            </span>
                                        @else
                                            <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400">
                                                {{ __('Sementara') }}
                                            </span>
                                        @endif
                                    </div>

                                    <h4 class="font-extrabold text-sm sm:text-base text-slate-900 dark:text-slate-100 line-clamp-1">
                                        {{ $maskIdentity ? __('Peserta :label', ['label' => $harapanLabel]) : $participant->displayName() }}
                                    </h4>

                                    <div class="mt-1 flex items-center gap-2">
                                        @if (!$maskIdentity)
                                            <span class="text-xs font-mono font-medium text-slate-400 dark:text-slate-500">
                                                {{ $participant->npp }}
                                            </span>
                                            <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                                        @endif
                                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                            {{ $participant->school_type }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60">
                                    <div class="flex items-baseline justify-between mb-1.5">
                                        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">
                                            {{ __('Akumulasi Nilai') }}
                                        </span>
                                        <span class="text-2xl sm:text-3xl font-black tracking-tight {{ $theme['score'] }}">
                                            {{ $participant->total_score ?? 0 }}
                                        </span>
                                    </div>

                                    {{-- Relative Score Progress Bar with Shimmer --}}
                                    <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-slate-700/60 overflow-hidden relative">
                                        <div class="h-full rounded-full bg-gradient-to-r {{ $theme['bar'] }} transition-all duration-700 relative overflow-hidden"
                                            style="width: {{ $percentage }}%">
                                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-shimmer"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    @endif
</div>
