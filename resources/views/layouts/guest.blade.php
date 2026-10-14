<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.meta')
        @include('partials.theme-init')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen lg:flex bg-gray-100 dark:bg-gray-900">
            <!-- Branding panel (desktop only) -->
            <div class="hidden lg:flex lg:w-3/5 relative overflow-hidden bg-gradient-to-br from-red-700 via-red-600 to-rose-900 flex-col justify-between p-12 text-white">
                <div class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(circle, white 1.5px, transparent 1.5px); background-size: 28px 28px;"></div>
                <div class="absolute -top-24 -right-16 h-96 w-96 rounded-full bg-white/10"></div>
                <div class="absolute bottom-0 -left-20 h-72 w-72 rounded-full bg-white/10"></div>

                <div class="relative flex items-center gap-3">
                    <a href="/" wire:navigate>
                        <x-application-logo class="h-12 w-12 rounded-full ring-4 ring-white/70 shadow-lg" />
                    </a>
                    <div>
                        <span class="block text-xl font-bold leading-tight">{{ config('app.name') }}</span>
                        <span class="block text-xs font-medium text-red-100 uppercase tracking-wide">{{ config('app.owner') }}</span>
                    </div>
                </div>

                <div class="relative">
                    <h1 class="text-4xl font-extrabold leading-tight">
                        {{ __('Penilaian Lomba yang') }}<br>{{ __('Adil & Transparan') }}
                    </h1>
                    <p class="mt-4 text-red-100 max-w-md">
                        {{ __('Aplikasi resmi :owner untuk menilai lomba-lomba yang diselenggarakan — dari pendataan peserta dan juri hingga pengumuman juara secara real-time.', ['owner' => config('app.owner')]) }}
                    </p>

                    <div class="mt-10 space-y-4">
                        @foreach ([
                            ['icon' => 'M8 21h8m-4-4v4M6 4h12v3a6 6 0 01-12 0V4zM6 6H4a2 2 0 002 4M18 6h2a2 2 0 01-2 4', 'text' => __('Kelola data Lomba, Kriteria, Juri & Peserta dalam satu sistem')],
                            ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'text' => __('Juri menilai langsung dari perangkat masing-masing berdasarkan NPP peserta')],
                            ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'text' => __('Live Rank & Dashboard tren penilaian secara real-time')],
                        ] as $feature)
                            <div class="flex items-center gap-3">
                                <span class="flex items-center justify-center h-9 w-9 rounded-full bg-white/15 shrink-0">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}" />
                                    </svg>
                                </span>
                                <span class="text-sm text-red-50">{{ $feature['text'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <p class="relative text-xs text-red-200">&copy; {{ date('Y') }} {{ config('app.name') }} &middot; {{ __('Bidang Humas dan Informatika Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan') }}</p>
            </div>

            <!-- Form panel -->
            <div class="relative flex-1 flex flex-col justify-center items-center px-4 pt-10 pb-20 lg:py-0 overflow-hidden">
                <!-- Mobile-only hero -->
                <div class="lg:hidden absolute inset-x-0 top-0 h-56 bg-gradient-to-br from-red-700 via-red-600 to-rose-900 overflow-hidden">
                    <div class="absolute -top-16 -right-16 h-64 w-64 rounded-full bg-white/10"></div>
                    <div class="absolute -top-24 -left-10 h-48 w-48 rounded-full bg-white/10"></div>
                </div>

                <x-dark-mode-toggle class="absolute top-4 right-4 lg:top-6 lg:right-6 h-9 w-9 text-white bg-white/15 hover:bg-white/25 lg:text-gray-500 dark:lg:text-gray-300 lg:bg-gray-100 dark:lg:bg-gray-700 lg:hover:bg-gray-200 dark:lg:hover:bg-gray-600" />

                <div class="relative lg:hidden text-center mb-6">
                    <a href="/" wire:navigate>
                        <x-application-logo class="w-16 h-16 rounded-full ring-4 ring-white/70 shadow-lg mx-auto" />
                    </a>
                    <h1 class="mt-3 text-lg font-bold text-white drop-shadow-sm">{{ config('app.name') }}</h1>
                    <p class="text-xs font-medium text-red-100 uppercase tracking-wide">{{ config('app.owner') }}</p>
                </div>

                <div class="relative w-full sm:max-w-sm">
                    <div class="hidden lg:block mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('Selamat Datang') }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Masuk untuk melanjutkan ke :app', ['app' => config('app.name')]) }}</p>
                    </div>

                    <div class="px-6 py-6 bg-white dark:bg-gray-800 shadow-xl overflow-hidden rounded-2xl">
                        {{ $slot }}
                    </div>
                </div>

                <!-- Copyright — stays pinned at the bottom on mobile, where the branding panel is hidden -->
                <p class="lg:hidden absolute bottom-4 inset-x-0 text-center text-xs text-gray-400 dark:text-gray-500 px-4">
                    &copy; {{ date('Y') }} {{ config('app.name') }} &middot; {{ __('Bidang Humas dan Informatika Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan') }}
                </p>
            </div>
        </div>
    </body>
</html>
