<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        @include('partials.meta', ['title' => __('Live Ranking Peringkat Lomba').' - '.config('app.name')])
        @include('partials.theme-init')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-100 text-slate-800 dark:bg-[#0b0f19] dark:text-slate-100 selection:bg-red-500 selection:text-white transition-colors duration-300 min-h-screen relative overflow-x-hidden">
        {{-- Ambient decorative lighting in background --}}
        <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
            <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] sm:w-[900px] h-[400px] bg-gradient-to-br from-red-600/20 via-rose-500/10 to-amber-500/15 blur-[120px] rounded-full dark:opacity-80"></div>
            <div class="absolute top-[40%] -left-32 w-96 h-96 bg-red-500/10 blur-[100px] rounded-full"></div>
            <div class="absolute top-[60%] -right-32 w-96 h-96 bg-yellow-500/10 blur-[100px] rounded-full"></div>
        </div>

        <div class="min-h-screen flex flex-col justify-between" x-data="{
            isFullscreen: false,
            toggleFullscreen() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().then(() => this.isFullscreen = true).catch(() => {});
                } else {
                    document.exitFullscreen().then(() => this.isFullscreen = false).catch(() => {});
                }
            }
        }" @fullscreenchange.window="isFullscreen = !!document.fullscreenElement">
            {{-- Top Broadcast Navigation --}}
            <header class="sticky top-0 z-40 backdrop-blur-md bg-white/80 dark:bg-[#0b0f19]/80 border-b border-slate-200/80 dark:border-slate-800/80 transition-colors">
                <div class="max-w-6xl mx-auto py-3 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <img src="{{ Vite::asset('resources/images/logo.png') }}" alt="{{ config('app.name') }}" class="h-9 w-9 rounded-full ring-2 ring-red-500/40 object-contain shadow-sm bg-white p-0.5">
                            <span class="absolute -bottom-0.5 -right-0.5 flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                            </span>
                        </div>
                        <div>
                            <span class="text-sm sm:text-base font-extrabold tracking-tight bg-gradient-to-r from-red-600 via-rose-600 to-amber-600 bg-clip-text text-transparent">
                                {{ config('app.name') }}
                            </span>
                            <span class="hidden sm:inline-block text-xs font-semibold text-slate-400 dark:text-slate-500 ml-2 border-l border-slate-200 dark:border-slate-700 pl-2">
                                {{ __('Papan Peringkat Resmi') }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Fullscreen / Proyektor Mode Button --}}
                        <button type="button" @click="toggleFullscreen()"
                            :title="isFullscreen ? '{{ __('Keluar Mode Layar Penuh') }}' : '{{ __('Mode Layar Penuh / Proyektor') }}'"
                            class="h-8 inline-flex items-center gap-1.5 px-3 rounded-full text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700/60 shadow-sm transition active:scale-95">
                            <svg x-show="!isFullscreen" class="h-4 w-4 shrink-0 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                            </svg>
                            <svg x-show="isFullscreen" class="h-4 w-4 shrink-0 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 9L4 4m0 0v4m0-4h4m6 5l5-5m0 0h-4m4 0v4M9 15l-5 5m0 0h4m-4 0v-4m11 0l5 5m0 0v-4m0 4h-4" />
                            </svg>
                            <span class="text-xs font-semibold" x-text="isFullscreen ? '{{ __('Layar Normal') }}' : '{{ __('Layar Penuh') }}'"></span>
                        </button>

                        {{-- Dark Mode Toggle --}}
                        <x-dark-mode-toggle class="h-8 w-8 text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700/60 shadow-sm" />
                    </div>
                </div>
            </header>

            {{-- Main Content --}}
            <main class="max-w-6xl mx-auto py-6 sm:py-8 px-4 sm:px-6 lg:px-8 flex-1 w-full">
                <livewire:live-rank.board :competition-id="$competitionId" />
            </main>

            {{-- Footer Broadcast Credit --}}
            <footer class="py-4 border-t border-slate-200/60 dark:border-slate-800/60 text-center text-xs text-slate-400 dark:text-slate-500">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }} &middot; {{ __('Sistem Penilaian Cepat & Papan Peringkat Realtime') }}</p>
            </footer>
        </div>
    </body>
</html>
