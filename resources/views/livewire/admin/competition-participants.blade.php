<div>
    <div class="sticky -top-6 z-10 -mx-6 -mt-6 px-6 pt-6 pb-3 bg-white dark:bg-gray-800 space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Peserta Lomba') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $competition->name }}</p>
            </div>
            <button type="button" wire:click="$parent.closeParticipants()"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
            <button type="button" wire:click="setTab('slot')"
                class="px-3 py-2 text-sm font-medium border-b-2 -mb-px whitespace-nowrap
                    {{ $activeTab === 'slot' ? 'border-red-600 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                {{ __('Buat Slot') }}
            </button>
            <button type="button" wire:click="setTab('registered')"
                class="px-3 py-2 text-sm font-medium border-b-2 -mb-px whitespace-nowrap
                    {{ $activeTab === 'registered' ? 'border-red-600 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                {{ __('Peserta Terdaftar (:count)', ['count' => $registrations->count()]) }}
            </button>
            <button type="button" wire:click="setTab('directory')"
                class="px-3 py-2 text-sm font-medium border-b-2 -mb-px whitespace-nowrap
                    {{ $activeTab === 'directory' ? 'border-red-600 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                {{ __('Tambah dari Direktori') }}
            </button>
        </div>
    </div>

    <div class="pt-5 space-y-5">
    @if ($activeTab === 'slot')
        <div>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('Buat Slot Massal') }}</h3>
            <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">
                {{ __('Belum tahu identitas sekolahnya? Isi jumlah kuota per jenjang, NPP & nama sementara dibuat otomatis. Identitas bisa disinkronkan kapan saja.') }}
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                @foreach ($competition->categories_list as $type)
                    <div>
                        <x-input-label :for="'slot-' . $type" :value="$type" />
                        <x-text-input wire:model="slotCounts.{{ $type }}" :id="'slot-' . $type" type="number"
                            min="0" class="block w-full mt-1" />
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
                <x-input-label :value="__('Format NPP')" />
                <div class="mt-1 flex gap-4">
                    <label class="flex items-center gap-2">
                        <input type="radio" wire:model="nppDigits" value="2"
                            class="dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-red-600 shadow-sm focus:ring-red-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('2 digit (01, 02, ...)') }}</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" wire:model="nppDigits" value="3"
                            class="dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-red-600 shadow-sm focus:ring-red-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('3 digit (001, 002, ...)') }}</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end mt-2">
                <x-primary-button type="button" wire:click="generateSlots">{{ __('Buat Slot') }}</x-primary-button>
            </div>
        </div>
    @elseif ($activeTab === 'registered')
        <div>
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    {{ __('Terdaftar (:count)', ['count' => $registrations->count()]) }}
                </h3>
                @if ($registrations->isNotEmpty())
                    <button type="button" wire:click="removeAll"
                        wire:confirm="{{ __('Keluarkan semua peserta dari lomba ini?') }}"
                        class="text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                        {{ __('Hapus Semua') }}
                    </button>
                @endif
            </div>

            @php $countsByType = $registrations->countBy('school_type'); @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 mb-3">
                @foreach ($competition->categories_list as $type)
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-2 text-center">
                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $countsByType->get($type, 0) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $type }}</p>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap gap-2 mb-2">
                <button type="button" wire:click="$set('registeredFilter', '')"
                    class="px-3 py-1.5 rounded-full text-sm font-medium {{ $registeredFilter === '' ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700' }}">
                    {{ __('Semua') }}
                </button>
                @foreach ($competition->categories_list as $type)
                    <button type="button" wire:click="$set('registeredFilter', '{{ $type }}')"
                        class="px-3 py-1.5 rounded-full text-sm font-medium {{ $registeredFilter === $type ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700' }}">
                        {{ $type }}
                    </button>
                @endforeach
            </div>

            <div
                class="max-h-[55vh] overflow-y-auto space-y-2 border border-gray-200 dark:border-gray-700 rounded-lg p-2">
                @forelse ($filteredRegistrations as $registration)
                    <div wire:key="reg-{{ $registration->id }}" class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                        {{ $registration->displayName() }}</p>
                                    <span
                                        class="shrink-0 inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        {{ $registration->school_type }}
                                    </span>
                                    @if (!$registration->participant_id)
                                        <span
                                            class="shrink-0 inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                            {{ __('Belum disinkronkan') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <button type="button" wire:click="remove('{{ $registration->id }}')"
                                class="shrink-0 h-7 w-7 flex items-center justify-center rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 dark:hover:text-red-400"
                                aria-label="{{ __('Hapus') }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="mt-2 flex items-center gap-2">
                            <label class="text-xs text-gray-400 dark:text-gray-500 shrink-0">{{ __('NPP') }}</label>
                            <input type="text" value="{{ $registration->npp }}"
                                wire:change="updateNpp('{{ $registration->id }}', $event.target.value)"
                                aria-label="{{ __('NPP') }}"
                                class="w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-red-500 focus:ring-red-500 rounded-lg shadow-sm">
                        </div>
                        @error("assignedNpp.{$registration->id}")
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror

                        <div class="mt-2 flex justify-end">
                            @if ($registration->participant_id)
                                <button type="button" wire:click="unsync('{{ $registration->id }}')"
                                    class="text-xs font-medium px-2.5 py-1 rounded-lg text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40">
                                    {{ __('Batalkan Sinkronisasi') }}
                                </button>
                            @else
                                <button type="button" wire:click="openSync('{{ $registration->id }}')"
                                    class="text-xs font-medium px-2.5 py-1 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40">
                                    {{ __('Sinkronkan') }}
                                </button>
                            @endif
                        </div>

                        @if ($syncingRegistration && $syncingRegistration->id === $registration->id)
                            <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700 space-y-2">
                                <x-text-input wire:model.live.debounce.300ms="syncSearch" type="text"
                                    class="block w-full text-sm" :placeholder="__('Cari peserta :type existing...', [
                                        'type' => $registration->school_type,
                                    ])" />

                                <div class="max-h-32 overflow-y-auto space-y-1">
                                    @forelse ($syncCandidates as $candidate)
                                        <button type="button" wire:key="cand-{{ $candidate->id }}"
                                            wire:click="syncExisting('{{ $registration->id }}', '{{ $candidate->id }}')"
                                            class="w-full text-left px-2 py-1.5 text-sm rounded-lg hover:bg-white dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                                            {{ $candidate->school_name }}
                                        </button>
                                    @empty
                                        <p class="text-xs text-gray-400 dark:text-gray-500 px-2">
                                            {{ __('Tidak ada peserta yang cocok.') }}</p>
                                    @endforelse
                                </div>

                                <div class="flex flex-col sm:flex-row gap-2 pt-1 border-t border-gray-200 dark:border-gray-700">
                                    <x-text-input wire:model="newSchoolName" type="text" class="block w-full text-sm"
                                        :placeholder="__('Atau ketik nama sekolah baru...')" />
                                    <x-secondary-button type="button" wire:click="syncNew('{{ $registration->id }}')"
                                        class="w-full sm:w-auto justify-center">
                                        {{ __('Buat & Kaitkan') }}
                                    </x-secondary-button>
                                </div>
                                @error('newSchoolName')
                                    <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror

                                <button type="button" wire:click="closeSync"
                                    class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                    {{ __('Batal') }}
                                </button>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 p-2">
                        {{ $registeredFilter ? __('Belum ada peserta :type terdaftar.', ['type' => $registeredFilter]) : __('Belum ada peserta terdaftar di lomba ini.') }}</p>
                @endforelse
            </div>
        </div>
    @elseif ($activeTab === 'directory')
        <div>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                {{ __('Tambah dari Direktori Peserta') }}</h3>
            <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">
                {{ __('Untuk peserta yang identitasnya sudah diketahui: centang lalu isi NPP pendaftarannya.') }}</p>

            <div class="flex flex-col sm:flex-row gap-2 mb-2">
                <x-text-input wire:model.live.debounce.300ms="search" type="text" class="block w-full"
                    :placeholder="__('Cari nama sekolah...')" />
                <select wire:model.live="school_type"
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-red-500 focus:ring-red-500 rounded-lg shadow-sm sm:w-44">
                    <option value="">{{ __('Semua Kategori') }}</option>
                    @foreach ($competition->categories_list as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="max-h-56 overflow-y-auto space-y-1 border border-gray-200 dark:border-gray-700 rounded-lg p-2">
                @forelse ($availableParticipants as $participant)
                    <div wire:key="available-{{ $participant->id }}"
                        class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-900/40">
                        <input type="checkbox" wire:model.live="selectedIds" value="{{ $participant->id }}"
                            class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-red-600 shadow-sm focus:ring-red-500">
                        <span class="min-w-0 flex-1">
                            <span
                                class="block text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $participant->school_name }}</span>
                            <span
                                class="block text-xs text-gray-500 dark:text-gray-400">{{ $participant->school_type }}</span>
                        </span>
                        <div class="w-24 shrink-0">
                            <input type="text" wire:model="npp.{{ $participant->id }}"
                                placeholder="{{ __('NPP') }}"
                                class="w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-red-500 focus:ring-red-500 rounded-lg shadow-sm">
                            @error("npp.{$participant->id}")
                                <p class="text-xs text-red-600 dark:text-red-400 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 p-2">
                        {{ __('Tidak ada peserta yang bisa ditambahkan.') }}</p>
                @endforelse
            </div>

            <div class="flex justify-end mt-3">
                <x-primary-button type="button" wire:click="addSelected" :disabled="empty($selectedIds)">
                    {{ __('Tambah Terpilih (:count)', ['count' => count($selectedIds)]) }}
                </x-primary-button>
            </div>
        </div>
    @endif
    </div>
</div>
