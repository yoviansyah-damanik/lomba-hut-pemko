<div class="space-y-6">
    @if ($competitions->isEmpty())
        <div
            class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4 sm:p-6 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Anda belum memiliki kriteria yang ditugaskan pada lomba yang sedang berlangsung.') }}
        </div>
    @else
        @if ($justSaved)
            <div class="rounded-2xl bg-green-50 dark:bg-green-900/40 p-4 text-sm text-green-700 dark:text-green-300">
                {{ __('Penilaian tersimpan. Siap cari peserta berikutnya.') }}
            </div>
        @endif

        @if ($searched && $registration)
            <div
                class="sticky top-16 z-30 -mx-4 sm:mx-0 px-4 sm:px-0 py-2 bg-gray-100/95 dark:bg-gray-900/95 backdrop-blur">
                <div
                    class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-3 sm:p-4 flex items-center gap-3">
                    <button type="button" wire:click="resetSearch"
                        class="shrink-0 h-10 w-10 rounded-full flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                        aria-label="{{ __('Kembali ke pencarian') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </button>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-gray-900 dark:text-gray-100 truncate">
                            {{ $registration->displayName() }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $registration->npp }} &middot;
                            {{ $registration->school_type }}</p>
                        <p class="text-xs text-red-600 dark:text-red-400 font-medium truncate">{{ $registration->competition->name }}</p>
                    </div>
                </div>
            </div>
        @else
            <div
                class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4 sm:p-6">
                <form wire:submit="search" class="space-y-4" autocomplete="off">
                    <div>
                        <x-input-label :value="__('Lomba')" />
                        <div class="mt-1 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($competitions as $item)
                                <label
                                    class="relative flex items-center gap-3 rounded-xl border-2 border-gray-300 dark:border-gray-700 p-3 cursor-pointer transition
                                        hover:border-red-300 dark:hover:border-red-700 hover:bg-gray-50 dark:hover:bg-gray-700/40
                                        has-[:checked]:border-red-600 has-[:checked]:bg-red-50 has-[:checked]:shadow-sm dark:has-[:checked]:bg-red-900/30">
                                    <input type="radio" wire:model="competitionId" value="{{ $item->id }}"
                                        class="peer sr-only">
                                    <span
                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 border-gray-300 dark:border-gray-600 transition peer-checked:border-red-600">
                                        <span
                                            class="h-2.5 w-2.5 rounded-full bg-red-600 scale-0 peer-checked:scale-100 transition-transform"></span>
                                    </span>
                                    <span
                                        class="text-sm font-medium truncate text-gray-700 dark:text-gray-300 peer-checked:text-red-700 dark:peer-checked:text-red-300">
                                        {{ $item->name }}
                                    </span>
                                    <svg
                                        class="ml-auto h-4 w-4 shrink-0 text-red-600 opacity-0 peer-checked:opacity-100 transition-opacity"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.704 5.29a1 1 0 010 1.415l-7.5 7.5a1 1 0 01-1.415 0l-3.5-3.5a1 1 0 111.415-1.414L8.5 12.086l6.79-6.795a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('competitionId')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <x-input-label for="npp" :value="__('NPP')" />
                            <x-text-input wire:model="npp" id="npp" class="block mt-1 w-full" type="text"
                                required autocomplete="off" />
                            <x-input-error :messages="$errors->get('npp')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="school_type" :value="__('Jenjang / Kategori')" />
                            <select wire:model="school_type" id="school_type" autocomplete="off"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm mt-1 block w-full">
                                @foreach ($this->currentCategories() as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('school_type')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        @if ($searched)
                            <x-secondary-button type="button"
                                wire:click="resetSearch">{{ __('Reset') }}</x-secondary-button>
                        @endif
                        <x-primary-button type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="search">{{ __('Cari Peserta') }}</span>
                            <span wire:loading wire:target="search">{{ __('Mencari...') }}</span>
                        </x-primary-button>
                    </div>
                </form>
            </div>
        @endif

        @if ($searched)
            @if ($registration)
                <livewire:evaluation.criterion-form :registration="$registration" :competition-id="$competitionId" :key="'criterion-form-' . $registration->id" />
            @else
                <div
                    class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4 sm:p-6 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Peserta dengan NPP dan jenis sekolah tersebut tidak ditemukan.') }}
                </div>
            @endif
        @endif
    @endif
</div>
