<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Hero Welcome Banner --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-red-700 via-rose-700 to-red-800 p-6 sm:p-8 text-white shadow-md">
                <div class="absolute -right-10 -bottom-10 h-44 w-44 rounded-full bg-white/10 blur-xl"></div>
                <div class="absolute right-20 -top-10 h-32 w-32 rounded-full bg-white/10 blur-lg"></div>
                <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-red-200">
                            <span>{{ auth()->user()->isAdmin() ? __('Panel Administrator') : __('Panel Dewan Juri') }}</span>
                            <span>&middot;</span>
                            <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                        </div>
                        <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight">
                            {{ __('Selamat Datang, :name!', ['name' => auth()->user()->name]) }}
                        </h1>
                        <p class="mt-1 text-sm text-red-100 max-w-xl">
                            @if (auth()->user()->isAdmin())
                                {{ __('Pantau progres penilaian lomba, kelola peserta dan dewan juri, serta terbitkan berita acara perlombaan.') }}
                            @else
                                {{ __('Pantau progres penilaian kriteria yang ditugaskan kepada Anda dan masukkan penilaian peserta dengan cepat.') }}
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.competitions') }}"
                                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-white text-red-700 text-xs font-bold uppercase tracking-wider shadow-sm hover:bg-red-50 active:scale-95 transition">
                                {{ __('Kelola Lomba') }} &rarr;
                            </a>
                        @else
                            <a href="{{ route('evaluation') }}"
                                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-white text-red-700 text-xs font-bold uppercase tracking-wider shadow-sm hover:bg-red-50 active:scale-95 transition">
                                {{ __('Mulai Menilai') }} &rarr;
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            @if (auth()->user()->isAdmin())
                <livewire:dashboard.admin-summary />
            @else
                <livewire:dashboard.judge-progress />
            @endif
        </div>
    </div>
</x-app-layout>
