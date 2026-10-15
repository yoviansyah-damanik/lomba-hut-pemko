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
            <div x-data="{
                showBackConfirm: false,
                hasInputValues() {
                    const inputs = document.querySelectorAll('input[type=\'number\'][id^=\'score-\']');
                    for (const inp of inputs) {
                        if (inp.value !== '') return true;
                    }
                    const textareas = document.querySelectorAll('textarea[id^=\'notes-\']');
                    for (const txt of textareas) {
                        if (txt.value.trim() !== '') return true;
                    }
                    return false;
                },
                requestBack() {
                    if (this.hasInputValues()) {
                        this.showBackConfirm = true;
                    } else {
                        this.confirmLeave();
                    }
                },
                confirmLeave() {
                    this.showBackConfirm = false;
                    $wire.resetSearch();
                },
                stayOnPage() {
                    this.showBackConfirm = false;
                    try {
                        history.pushState({ evaluating: true }, '');
                    } catch (e) {}
                },
                init() {
                    try {
                        history.pushState({ evaluating: true }, '');
                    } catch (e) {}

                    this.$watch('showBackConfirm', value => {
                        if (value) {
                            document.body.classList.add('overflow-y-hidden');
                        } else {
                            document.body.classList.remove('overflow-y-hidden');
                        }
                    });
                }
            }"
            @popstate.window="if (hasInputValues()) { showBackConfirm = true; try { history.pushState({ evaluating: true }, ''); } catch(e){} } else { confirmLeave(); }"
            @keydown.escape.window="if (showBackConfirm) stayOnPage()">
                {{-- Sticky Top Bar --}}
                <div class="sticky top-16 z-30 -mx-4 sm:mx-0 px-4 sm:px-0 py-2 bg-gray-100/95 dark:bg-gray-900/95 backdrop-blur">
                    <div
                        class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-3 sm:p-4 flex items-center gap-3">
                        <button type="button" @click="requestBack()"
                            class="shrink-0 h-10 w-10 rounded-full flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
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

                {{-- Modal Konfirmasi Meninggalkan Penilaian --}}
                <div x-show="showBackConfirm"
                    x-cloak
                    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex min-h-full items-center justify-center"
                    style="display: none;"
                    role="dialog"
                    aria-modal="true">
                    
                    <!-- Backdrop Overlay -->
                    <div x-show="showBackConfirm"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                        @click="stayOnPage()"></div>

                    <!-- Modal Dialog Card -->
                    <div x-show="showBackConfirm"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 sm:p-7 border border-gray-100 dark:border-gray-700 z-10 transition-all my-8 text-left">
                        
                        <div class="flex items-start gap-4">
                            <div class="h-12 w-12 rounded-2xl bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 shadow-inner">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    {{ __('Tinggalkan Penilaian Ini?') }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 leading-relaxed">
                                    {{ __('Anda sedang menilai peserta') }} <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $registration->displayName() }}</span> ({{ $registration->npp }}).
                                    {{ __('Nilai yang belum disimpan tetap aman tersimpan di draf lokal perangkat ini.') }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                    {{ __('Pilih "Lanjut Menilai" jika Anda tidak sengaja menekan tombol kembali.') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700/60 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                            <x-secondary-button type="button" @click="confirmLeave()" class="w-full sm:w-auto justify-center text-xs py-2.5">
                                {{ __('Ya, Kembali ke Pencarian') }}
                            </x-secondary-button>
                            <x-primary-button type="button" @click="stayOnPage()" class="w-full sm:w-auto justify-center text-xs py-2.5 shadow-md">
                                {{ __('Lanjut Menilai') }}
                            </x-primary-button>
                        </div>
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
                                    <input type="radio" wire:model.live="competitionId" value="{{ $item->id }}"
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
                            <select wire:model="school_type" id="school_type" wire:key="select-category-{{ $competitionId }}" autocomplete="off"
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
