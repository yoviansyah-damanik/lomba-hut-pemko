<div>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Daftar Lomba') }}</h3>
        <x-primary-button wire:click="create">{{ __('Tambah Lomba') }}</x-primary-button>
    </div>

    @php
        $statusLabels = [
            'upcoming' => __('Akan Datang'),
            'active' => __('Berlangsung'),
            'ended' => __('Selesai'),
        ];
    @endphp

    <div class="flex flex-wrap items-center gap-2 mb-4">
        <div class="flex flex-wrap gap-2">
            <button wire:click="$set('filterStatus', '')"
                class="px-3 py-1.5 rounded-full text-sm font-medium {{ $filterStatus === '' ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700' }}">
                {{ __('Semua') }}
            </button>
            @foreach (\App\Livewire\Admin\Competition::STATUSES as $status)
                <button wire:click="$set('filterStatus', '{{ $status }}')"
                    class="px-3 py-1.5 rounded-full text-sm font-medium {{ $filterStatus === $status ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700' }}">
                    {{ $statusLabels[$status] }}
                </button>
            @endforeach
        </div>

        <div class="relative ms-auto w-full sm:w-64">
            <input type="search" wire:model.live.debounce.400ms="search" placeholder="{{ __('Cari nama lomba...') }}"
                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-red-500 focus:ring-red-500 rounded-lg shadow-sm w-full text-sm">
        </div>
    </div>

    <div class="space-y-4">
        @forelse ($competitions as $competition)
            @php $isActive = $competition->isActive(); @endphp
            <div
                class="bg-white dark:bg-gray-800 shadow-sm ring-1 {{ $isActive ? 'ring-red-200 dark:ring-red-900/60' : 'ring-gray-100 dark:ring-gray-700' }} rounded-2xl overflow-hidden">
                <div class="p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex items-start gap-3">
                            <span
                                class="flex items-center justify-center h-11 w-11 rounded-full bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-300 shrink-0">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 21h8m-4-4v4M6 4h12v3a6 6 0 01-12 0V4zM6 6H4a2 2 0 002 4M18 6h2a2 2 0 01-2 4" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 dark:text-gray-100 truncate">
                                    {{ $competition->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $competition->start_time->translatedFormat('d M Y H:i') }} &ndash;
                                    {{ $competition->end_time->translatedFormat('d M Y H:i') }}
                                </p>
                            </div>
                        </div>
                        @if ($isActive)
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 shrink-0">
                                <span class="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                {{ __('Berlangsung') }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="toggleRankOpen('{{ $competition->id }}')"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $competition->rank_open
                                    ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300'
                                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                            <span
                                class="h-1.5 w-1.5 rounded-full {{ $competition->rank_open ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                            {{ $competition->rank_open ? __('Live Rank Terbuka') : __('Live Rank Ditutup') }}
                        </button>

                        <button type="button" wire:click="toggleHideIdentity('{{ $competition->id }}')"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $competition->hide_identity
                                    ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'
                                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                            <span
                                class="h-1.5 w-1.5 rounded-full {{ $competition->hide_identity ? 'bg-amber-500' : 'bg-gray-400' }}"></span>
                            {{ $competition->hide_identity ? __('Identitas Disembunyikan') : __('Identitas Tampil') }}
                        </button>
                    </div>
                </div>

                <div
                    class="flex flex-wrap items-center gap-2 px-4 sm:px-5 py-3 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="toggleCriteria('{{ $competition->id }}')"
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                        {{ __(':count kriteria', ['count' => $competition->criteria_count]) }}
                        <svg class="h-4 w-4 transition-transform {{ $expandedId === $competition->id ? 'rotate-180' : '' }}"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div class="flex items-center gap-2 ms-auto flex-wrap">
                        <x-action-button wire:click="manageParticipants('{{ $competition->id }}')">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-2.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-3-6.65" />
                            </svg>
                            {{ __('Peserta') }}
                            <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                {{ $competition->registrations_count }}
                            </span>
                        </x-action-button>
                        <x-action-button :href="route('live-rank.show', $competition)" target="_blank">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 19V6l12-3v13M9 19a3 3 0 11-6 0 3 3 0 016 0zm12-3a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ __('Live Rank') }}
                        </x-action-button>
                        <x-action-button :href="route('admin.recap', $competition)">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 17h6M9 13h6m-6-4h1m-4 12h12a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-4.414-4.414A1 1 0 0014.586 2H6a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            {{ __('Rekap') }}
                        </x-action-button>
                        <x-action-button wire:click="edit('{{ $competition->id }}')">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ __('Edit') }}
                        </x-action-button>
                        <x-action-button variant="danger" wire:click="delete('{{ $competition->id }}')"
                            wire:confirm="{{ __('Hapus lomba ini beserta kriterianya?') }}">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            {{ __('Hapus') }}
                        </x-action-button>
                    </div>
                </div>

                @if ($expandedId === $competition->id)
                    <div
                        class="bg-gray-50 dark:bg-gray-900/40 p-4 sm:p-5 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                {{ __('Kriteria untuk :name', ['name' => $competition->name]) }}</h4>
                            <x-secondary-button
                                wire:click="createCriterion">{{ __('Tambah Kriteria') }}</x-secondary-button>
                        </div>

                        <div class="space-y-2">
                            @forelse ($expandedCriteria as $criterion)
                                <div
                                    class="flex items-center justify-between gap-4 p-3 bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-xl">
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                            {{ $criterion->name }}</p>
                                        <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                            <span
                                                class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 ring-1 ring-red-200 dark:ring-red-800">
                                                {{ __('Nilai: :min - :max', ['min' => $criterion->min_score, 'max' => $criterion->max_score]) }}
                                            </span>
                                            @if ($criterion->items->isNotEmpty())
                                                <span
                                                    class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300 ring-1 ring-violet-200 dark:ring-violet-800">
                                                    {{ __(':count Sub-Item Rubrik', ['count' => $criterion->items->count()]) }}
                                                </span>
                                            @endif
                                            @foreach ($criterion->school_types as $type)
                                                <span
                                                    class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                    {{ $type }}
                                                </span>
                                            @endforeach
                                        </div>
                                        @php
                                            $judgesByType = $criterion->judges->groupBy('pivot.school_type');
                                        @endphp
                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1 space-y-0.5">
                                            @if ($criterion->judges->isEmpty())
                                                <p>{{ __('Belum ada juri ditugaskan') }}</p>
                                            @else
                                                @foreach ($criterion->school_types as $type)
                                                    <p class="truncate">
                                                        <span class="font-medium text-gray-600 dark:text-gray-300">{{ $type }}:</span>
                                                        @if ($judgesByType->get($type, collect())->isEmpty())
                                                            <span class="text-amber-600 dark:text-amber-400">{{ __('belum ada juri') }}</span>
                                                        @else
                                                            {{ $judgesByType->get($type)->take(\App\Livewire\Admin\Competition::MAX_DISPLAYED_JUDGES)->pluck('name')->join(', ') }}
                                                            @if ($judgesByType->get($type)->count() > \App\Livewire\Admin\Competition::MAX_DISPLAYED_JUDGES)
                                                                <button type="button"
                                                                    wire:click="viewJudges('{{ $criterion->id }}')"
                                                                    class="underline font-medium">
                                                                    {{ __('+:count lainnya', ['count' => $judgesByType->get($type)->count() - \App\Livewire\Admin\Competition::MAX_DISPLAYED_JUDGES]) }}
                                                                </button>
                                                            @endif
                                                        @endif
                                                    </p>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <x-action-button wire:click="editCriterion('{{ $criterion->id }}')">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            {{ __('Edit') }}
                                        </x-action-button>
                                        <x-action-button variant="danger"
                                            wire:click="deleteCriterion('{{ $criterion->id }}')"
                                            wire:confirm="{{ __('Hapus kriteria ini?') }}">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            {{ __('Hapus') }}
                                        </x-action-button>
                                    </div>
                                </div>
                            @empty
                                <p class="p-3 text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('Belum ada kriteria untuk lomba ini.') }}</p>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div
                class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                {{ $search || $filterStatus ? __('Tidak ada lomba yang cocok dengan filter.') : __('Belum ada lomba terdaftar.') }}
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $competitions->links() }}
    </div>

    <x-simple-modal :show="$showModal" max-width="max-w-3xl">
        <form wire:submit="save" class="space-y-4">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ $editing ? __('Edit Lomba') : __('Tambah Lomba') }}
            </h2>

            <div>
                <x-input-label for="name" :value="__('Nama Lomba')" />
                <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" required
                    autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            @php
                $timeSelectClass =
                    'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-red-500 focus:ring-red-500 rounded-lg shadow-sm mt-1';
            @endphp

            <div>
                <x-input-label for="start_date" :value="__('Mulai')" />
                <div class="mt-1 flex gap-2">
                    <x-text-input wire:model="start_date" id="start_date" class="block w-full" type="date"
                        required />
                    <select wire:model="start_hour" aria-label="{{ __('Jam mulai') }}"
                        class="{{ $timeSelectClass }} w-20">
                        @foreach (range(0, 23) as $hour)
                            <option value="{{ sprintf('%02d', $hour) }}">{{ sprintf('%02d', $hour) }}</option>
                        @endforeach
                    </select>
                    <span class="mt-1 self-center text-gray-400">:</span>
                    <select wire:model="start_minute" aria-label="{{ __('Menit mulai') }}"
                        class="{{ $timeSelectClass }} w-20">
                        @foreach (range(0, 59) as $minute)
                            <option value="{{ sprintf('%02d', $minute) }}">{{ sprintf('%02d', $minute) }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('Format 24 jam') }}</p>
                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                <x-input-error :messages="$errors->get('start_hour')" class="mt-2" />
                <x-input-error :messages="$errors->get('start_minute')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="end_date" :value="__('Selesai')" />
                <div class="mt-1 flex gap-2">
                    <x-text-input wire:model="end_date" id="end_date" class="block w-full" type="date"
                        required />
                    <select wire:model="end_hour" aria-label="{{ __('Jam selesai') }}"
                        class="{{ $timeSelectClass }} w-20">
                        @foreach (range(0, 23) as $hour)
                            <option value="{{ sprintf('%02d', $hour) }}">{{ sprintf('%02d', $hour) }}</option>
                        @endforeach
                    </select>
                    <span class="mt-1 self-center text-gray-400">:</span>
                    <select wire:model="end_minute" aria-label="{{ __('Menit selesai') }}"
                        class="{{ $timeSelectClass }} w-20">
                        @foreach (range(0, 59) as $minute)
                            <option value="{{ sprintf('%02d', $minute) }}">{{ sprintf('%02d', $minute) }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('Format 24 jam') }}</p>
                <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                <x-input-error :messages="$errors->get('end_hour')" class="mt-2" />
                <x-input-error :messages="$errors->get('end_minute')" class="mt-2" />
            </div>

            <div class="flex justify-end gap-2">
                <x-secondary-button type="button"
                    wire:click="$set('showModal', false)">{{ __('Batal') }}</x-secondary-button>
                <x-primary-button type="submit">{{ __('Simpan') }}</x-primary-button>
            </div>
        </form>
    </x-simple-modal>

    <x-simple-modal :show="$showCriterionModal" max-width="max-w-3xl" show-property="showCriterionModal">
        <form wire:submit="saveCriterion" class="space-y-4">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ $editingCriterion ? __('Edit Kriteria') : __('Tambah Kriteria') }}
            </h2>

            <div>
                <x-input-label for="criterion_name" :value="__('Nama Kriteria')" />
                <x-text-input wire:model="criterion_name" id="criterion_name" class="block mt-1 w-full"
                    type="text" required autofocus />
                <x-input-error :messages="$errors->get('criterion_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="criterion_description" :value="__('Deskripsi')" />
                <textarea wire:model="criterion_description" id="criterion_description" rows="2"
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm mt-1 block w-full"></textarea>
                <x-input-error :messages="$errors->get('criterion_description')" class="mt-2" />
            </div>

            {{-- Toggle Sub-Item Penilaian (Rubrik) --}}
            <div class="p-3 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" wire:model.live="has_sub_items"
                        class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-red-600 shadow-sm focus:ring-red-500">
                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('Gunakan Sub-Item Penilaian (Rubrik)') }}</span>
                </label>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    {{ __('Jika diaktifkan, juri akan menginput nilai per item dan nilai kriteria akan otomatis terhitung dari akumulasi item tersebut.') }}
                </p>
            </div>

            @if ($has_sub_items)
                {{-- Form Sub-Items --}}
                <div class="space-y-3 p-3.5 bg-red-50/40 dark:bg-red-950/20 rounded-xl border border-red-100 dark:border-red-900/40">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-red-700 dark:text-red-300">
                            {{ __('Daftar Sub-Item Rubrik') }}
                        </span>
                        <button type="button" wire:click="addSubItem"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 dark:text-red-400 hover:text-red-700">
                            + {{ __('Tambah Item') }}
                        </button>
                    </div>

                    @error('subItems')
                        <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    <div class="space-y-2">
                        @foreach ($subItems as $idx => $item)
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 p-2.5 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <span class="text-xs font-bold text-gray-400 w-5 shrink-0">{{ $idx + 1 }}.</span>
                                <div class="flex-1 w-full sm:w-auto">
                                    <input type="text" wire:model="subItems.{{ $idx }}.name"
                                        placeholder="{{ __('Nama item (contoh: Artikulasi & Intonasi)') }}"
                                        class="w-full text-xs rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-red-500 focus:ring-red-500">
                                    @error('subItems.' . $idx . '.name')
                                        <p class="text-[10px] text-red-600 mt-0.5">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                                    <div class="w-20">
                                        <input type="number" wire:model.live="subItems.{{ $idx }}.min_score" min="0" placeholder="Min"
                                            class="w-full text-xs text-center rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-red-500 focus:ring-red-500">
                                    </div>
                                    <span class="text-xs text-gray-400">-</span>
                                    <div class="w-20">
                                        <input type="number" wire:model.live="subItems.{{ $idx }}.max_score" min="0" placeholder="Max"
                                            class="w-full text-xs text-center rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-red-500 focus:ring-red-500">
                                    </div>
                                    <button type="button" wire:click="removeSubItem({{ $idx }})"
                                        class="p-1 text-gray-400 hover:text-red-600 transition" title="{{ __('Hapus item') }}">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-1">
                        <span>{{ __('Akumulasi Nilai Kriteria:') }}</span>
                        <span class="font-bold text-red-600 dark:text-red-400">
                            {{ $criterion_min_score }} &ndash; {{ $criterion_max_score }}
                        </span>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="criterion_min_score" :value="__('Nilai Minimal')" />
                        <x-text-input wire:model="criterion_min_score" id="criterion_min_score" class="block mt-1 w-full"
                            type="number" min="0" required />
                        <x-input-error :messages="$errors->get('criterion_min_score')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="criterion_max_score" :value="__('Nilai Maksimal')" />
                        <x-text-input wire:model="criterion_max_score" id="criterion_max_score" class="block mt-1 w-full"
                            type="number" min="0" required />
                        <x-input-error :messages="$errors->get('criterion_max_score')" class="mt-2" />
                    </div>
                </div>
            @endif

            <div>
                <x-input-label :value="__('Jenjang Sekolah')" />
                <div class="mt-1 flex flex-wrap gap-4">
                    @foreach (\App\Livewire\Admin\Competition::SCHOOL_TYPES as $type)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model.live="criterionSchoolTypes" value="{{ $type }}"
                                class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-red-600 shadow-sm focus:ring-red-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $type }}</span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('criterionSchoolTypes')" class="mt-2" />
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                    {{ __('Jenjang yang dicentang wajib punya minimal satu juri penilai.') }}
                </p>
            </div>

            @foreach (\App\Livewire\Admin\Competition::SCHOOL_TYPES as $type)
                @if (in_array($type, $criterionSchoolTypes, true))
                    <div>
                        <x-input-label :value="__('Juri Penilai — :type', ['type' => $type])" />
                        <div
                            class="mt-1 space-y-1 max-h-40 overflow-y-auto border border-gray-300 dark:border-gray-700 rounded-md p-2">
                            @forelse ($judges as $judge)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model="judgeIdsBySchoolType.{{ $type }}" value="{{ $judge->id }}"
                                        class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $judge->name }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Belum ada juri terdaftar.') }}</p>
                            @endforelse
                        </div>
                        <x-input-error :messages="$errors->get('judgeIdsBySchoolType.'.$type)" class="mt-2" />
                    </div>
                @endif
            @endforeach

            <div class="flex justify-end gap-2">
                <x-secondary-button type="button"
                    wire:click="$set('showCriterionModal', false)">{{ __('Batal') }}</x-secondary-button>
                <x-primary-button type="submit">{{ __('Simpan') }}</x-primary-button>
            </div>
        </form>
    </x-simple-modal>

    <x-simple-modal :show="$showParticipantsModal" max-width="max-w-3xl" show-property="showParticipantsModal">
        @if ($managingParticipantsFor)
            <livewire:admin.competition-participants :competition-id="$managingParticipantsFor" :key="$managingParticipantsFor" lazy />
        @endif
    </x-simple-modal>

    <x-simple-modal :show="$showJudgesModal" max-width="max-w-3xl" show-property="showJudgesModal">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
            {{ __('Juri Penilai :name', ['name' => $judgesModalTitle]) }}
        </h2>

        <ul class="space-y-1 max-h-60 overflow-y-auto">
            @foreach ($judgesModalNames as $judgeName)
                <li class="text-sm text-gray-700 dark:text-gray-300">{{ $judgeName }}</li>
            @endforeach
        </ul>

        <div class="flex justify-end mt-4">
            <x-secondary-button type="button"
                wire:click="$set('showJudgesModal', false)">{{ __('Tutup') }}</x-secondary-button>
        </div>
    </x-simple-modal>
</div>
