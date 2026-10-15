<div class="p-4 space-y-6 bg-white shadow-sm dark:bg-gray-800 ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl sm:p-6"
    @if ($criteria->isNotEmpty())
        x-data="{
            storageKey: 'eval_draft_{{ $competitionId }}_{{ $registration->id }}',
            draftRestored: false,
            init() {
                // 1. Auto-focus ke input nilai pertama
                this.$nextTick(() => {
                    const firstInput = this.$el.querySelector('input[type=\'number\']');
                    if (firstInput) {
                        firstInput.focus();
                        firstInput.select();
                    }
                });

                // 2. Auto-save restore dari LocalStorage
                try {
                    const saved = localStorage.getItem(this.storageKey);
                    if (saved) {
                        const data = JSON.parse(saved);
                        let restored = false;
                        if (data.scores) {
                            for (const [cid, val] of Object.entries(data.scores)) {
                                const input = document.getElementById('score-' + cid);
                                if (input && val !== null && val !== undefined && val !== '') {
                                    if (!input.value) {
                                        input.value = val;
                                        input.dispatchEvent(new Event('input', { bubbles: true }));
                                        restored = true;
                                    }
                                }
                            }
                        }
                        if (data.notes) {
                            for (const [cid, val] of Object.entries(data.notes)) {
                                const textarea = document.getElementById('notes-' + cid);
                                if (textarea && val && !textarea.value) {
                                    textarea.value = val;
                                    textarea.dispatchEvent(new Event('input', { bubbles: true }));
                                    restored = true;
                                }
                            }
                        }
                        if (restored) {
                            this.draftRestored = true;
                        }
                    }
                } catch (e) {}
            },
            saveDraft() {
                try {
                    const draft = { scores: {}, notes: {}, time: Date.now() };
                    this.$el.querySelectorAll('input[type=\'number\'][id^=\'score-\']').forEach(input => {
                        const cid = input.id.replace('score-', '');
                        if (input.value !== '') draft.scores[cid] = input.value;
                    });
                    this.$el.querySelectorAll('textarea[id^=\'notes-\']').forEach(textarea => {
                        const cid = textarea.id.replace('notes-', '');
                        if (textarea.value.trim() !== '') draft.notes[cid] = textarea.value.trim();
                    });
                    localStorage.setItem(this.storageKey, JSON.stringify(draft));
                } catch (e) {}
            },
            clearDraft() {
                try {
                    localStorage.removeItem(this.storageKey);
                    this.draftRestored = false;
                } catch (e) {}
            }
        }"
        @input.debounce.500ms="saveDraft()"
        @evaluation-saved.window="clearDraft()"
    @endif>
    {{-- <div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $registration->displayName() }}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $registration->npp }} &middot;
            {{ $registration->school_type }}</p>
    </div> --}}

    @if ($criteria->isEmpty())
        <div class="flex items-start gap-3 rounded-xl bg-amber-50 dark:bg-amber-900/30 p-4">
            <svg class="h-5 w-5 shrink-0 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <p class="text-sm text-amber-800 dark:text-amber-300">
                {{ __('Anda tidak dapat menilai peserta ini karena belum ada kriteria yang ditugaskan untuk jenjang/kategori :type pada lomba ini. Hubungi admin untuk menugaskan kriteria.', ['type' => $registration->school_type]) }}
            </p>
        </div>
    @else
        <div x-show="draftRestored" x-transition class="flex items-center justify-between p-3 text-xs rounded-xl bg-sky-50 dark:bg-sky-900/30 text-sky-800 dark:text-sky-300">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-sky-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ __('Draft nilai belum tersimpan otomatis dipulihkan dari penyimpanan lokal perangkat ini.') }}</span>
            </div>
            <button type="button" @click="clearDraft(); window.location.reload();" class="underline text-sky-600 dark:text-sky-400 hover:text-sky-800">
                {{ __('Buang draft') }}
            </button>
        </div>

        <form wire:submit="review" class="space-y-4" autocomplete="off">
            @foreach ($criteria as $criterion)
                <div class="p-4 space-y-3 ring-1 ring-gray-200 dark:ring-gray-700 rounded-xl">
                    <div>
                        <x-input-label :value="$criterion->name" />
                        @if ($criterion->description)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $criterion->description }}</p>
                        @endif
                    </div>

                    @if ($criterion->items->isNotEmpty())
                        {{-- Mode Rubrik Sub-Item --}}
                        <div class="space-y-3 p-3.5 bg-gray-50/80 dark:bg-gray-900/40 rounded-xl border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    {{ __('Rincian Sub-Item Penilaian') }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300">
                                    <span>{{ __('Akumulasi:') }}</span>
                                    <span class="text-sm font-black">{{ $scores[$criterion->id] ?? 0 }}</span>
                                    <span class="text-[10px] text-gray-500 font-normal">/ {{ $criterion->max_score }}</span>
                                </span>
                            </div>

                            <div class="space-y-2.5">
                                @foreach ($criterion->items as $item)
                                    <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm"
                                        x-data="{
                                            min: {{ $item->min_score }},
                                            max: {{ $item->max_score }},
                                            step(delta) {
                                                const input = $refs.itemInput;
                                                let val = parseInt(input.value);
                                                if (isNaN(val)) {
                                                    val = delta > 0 ? this.min : this.max;
                                                } else {
                                                    val += delta;
                                                }
                                                input.value = Math.min(this.max, Math.max(this.min, val));
                                                input.dispatchEvent(new Event('input', { bubbles: true }));
                                            }
                                        }">
                                        <div class="flex items-center justify-between gap-2 mb-1.5">
                                            <p class="text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 truncate">
                                                {{ $loop->iteration }}. {{ $item->name }}
                                            </p>
                                            <span class="text-[11px] font-medium text-gray-400 shrink-0">
                                                ({{ $item->min_score }} &ndash; {{ $item->max_score }})
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="step(-1)"
                                                class="text-lg font-bold text-gray-600 border border-gray-300 rounded-lg shrink-0 h-10 w-10 dark:border-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 active:bg-gray-100 select-none">
                                                &minus;
                                            </button>
                                            <input wire:model.live.debounce.300ms="itemScores.{{ $criterion->id }}.{{ $item->id }}"
                                                x-ref="itemInput"
                                                id="score-item-{{ $criterion->id }}-{{ $item->id }}"
                                                type="number" min="{{ $item->min_score }}" max="{{ $item->max_score }}"
                                                class="w-full text-lg font-bold text-center border-gray-300 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-red-500 focus:ring-red-500">
                                            <button type="button" @click="step(1)"
                                                class="text-lg font-bold text-gray-600 border border-gray-300 rounded-lg shrink-0 h-10 w-10 dark:border-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 active:bg-gray-100 select-none">
                                                +
                                            </button>
                                        </div>
                                        <x-input-error :messages="$errors->get('itemScores.' . $criterion->id . '.' . $item->id)" class="mt-1" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        {{-- Mode Normal (Nilai Langsung) --}}
                        <div>
                            <x-input-label :for="'score-' . $criterion->id" :value="__('Nilai (:min - :max)', [
                                'min' => $criterion->min_score,
                                'max' => $criterion->max_score,
                            ])" />
                            <div class="flex items-center gap-3 mt-1" x-data="{
                                min: {{ $criterion->min_score }},
                                max: {{ $criterion->max_score }},
                                step(delta) {
                                    const input = $refs.scoreInput;
                                    let val = parseInt(input.value);
                                    if (isNaN(val)) {
                                        val = delta > 0 ? this.min : this.max;
                                    } else {
                                        val += delta;
                                    }
                                    input.value = Math.min(this.max, Math.max(this.min, val));
                                    input.dispatchEvent(new Event('input', { bubbles: true }));
                                }
                            }">
                                <button type="button" @click="step(-1)"
                                    class="text-xl font-bold text-gray-600 border border-gray-300 rounded-lg shrink-0 h-11 w-11 dark:border-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 active:bg-gray-100 dark:active:bg-gray-600 select-none">
                                    &minus;
                                </button>
                                <input wire:model="scores.{{ $criterion->id }}" x-ref="scoreInput"
                                    id="score-{{ $criterion->id }}" type="number" min="{{ $criterion->min_score }}"
                                    max="{{ $criterion->max_score }}" @if ($loop->first) autofocus @endif autocomplete="off"
                                    class="w-full text-xl font-bold text-center border-gray-300 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-red-500 focus:ring-red-500 scroll-mt-36">
                                <button type="button" @click="step(1)"
                                    class="text-xl font-bold text-gray-600 border border-gray-300 rounded-lg shrink-0 h-11 w-11 dark:border-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 active:bg-gray-100 dark:active:bg-gray-600 select-none">
                                    +
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('scores.' . $criterion->id)" class="mt-2" />
                        </div>
                    @endif

                    <div x-data="{
                        listening: false,
                        manualStop: true,
                        supported: 'webkitSpeechRecognition' in window || 'SpeechRecognition' in window,
                        rec: null,
                        committedText: '',
                        interimText: '',
                        restartTimer: null,

                        init() {
                            if (typeof this.$cleanup === 'function') {
                                this.$cleanup(() => this.stopListening());
                            }
                        },

                        cleanWord(word) {
                            return (word || '').toLowerCase().replace(/[.,/#!$%^&*;:{}=\-_`~()?'\x22]/g, '').trim();
                        },

                        // Menggabungkan dua teks dengan deteksi overlap cerdas agar tidak terjadi duplikasi kata
                        mergeText(prev, next) {
                            prev = (prev || '').trim();
                            next = (next || '').trim();
                            if (!prev) return next;
                            if (!next) return prev;

                            const normPrev = prev.toLowerCase().replace(/\s+/g, ' ');
                            const normNext = next.toLowerCase().replace(/\s+/g, ' ');

                            // 1. Jika teks baru identik atau sudah ada di akhir teks lama
                            if (normPrev === normNext || normPrev.endsWith(normNext)) {
                                return prev;
                            }

                            // 2. Jika teks baru merupakan versi kumulatif yang diawali teks lama (umum di Android/Chrome)
                            if (normNext.startsWith(normPrev)) {
                                return next;
                            }

                            // 3. Deteksi overlap berbasis kata pada sambungan akhir prev dan awal next
                            const prevWords = prev.split(/\s+/);
                            const nextWords = next.split(/\s+/);
                            const maxOverlap = Math.min(prevWords.length, nextWords.length, 10);

                            for (let len = maxOverlap; len > 0; len--) {
                                const prevSlice = prevWords.slice(prevWords.length - len);
                                const nextSlice = nextWords.slice(0, len);

                                const prevMatch = prevSlice.map(w => this.cleanWord(w)).join(' ');
                                const nextMatch = nextSlice.map(w => this.cleanWord(w)).join(' ');

                                if (prevMatch === nextMatch && prevMatch.length > 0) {
                                    const remainingNext = nextWords.slice(len).join(' ');
                                    return remainingNext ? `${prev} ${remainingNext}` : prev;
                                }
                            }

                            // 4. Tidak ada overlap kata, gabungkan dengan spasi
                            return `${prev} ${next}`;
                        },

                        applyText(text) {
                            this.$refs.notesInput.value = text;
                            this.$refs.notesInput.dispatchEvent(new Event('input', { bubbles: true }));
                        },

                        createRecognition() {
                            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
                            if (!SR) return null;
                            const rec = new SR();
                            rec.lang = 'id-ID';
                            rec.continuous = true;
                            rec.interimResults = true;
                            rec.maxAlternatives = 1;

                            rec.onresult = (e) => {
                                let sessionFinal = '';
                                let sessionInterim = '';

                                for (let i = e.resultIndex; i < e.results.length; i++) {
                                    const item = e.results[i];
                                    const transcript = item[0]?.transcript?.trim() || '';
                                    if (!transcript) continue;

                                    if (item.isFinal) {
                                        sessionFinal = this.mergeText(sessionFinal, transcript);
                                    } else {
                                        sessionInterim = this.mergeText(sessionInterim, transcript);
                                    }
                                }

                                if (sessionFinal) {
                                    this.committedText = this.mergeText(this.committedText, sessionFinal);
                                }

                                this.interimText = sessionInterim;

                                const currentDisplay = this.interimText
                                    ? this.mergeText(this.committedText, this.interimText)
                                    : this.committedText;

                                this.applyText(currentDisplay);
                            };

                            rec.onerror = (e) => {
                                if (e.error === 'not-allowed') {
                                    this.stopListening();
                                }
                            };

                            rec.onend = () => {
                                // Jangan simpan interimText ke committedText saat jeda/reconnect
                                this.interimText = '';

                                if (this.listening && !this.manualStop) {
                                    clearTimeout(this.restartTimer);
                                    this.restartTimer = setTimeout(() => {
                                        if (this.listening && !this.manualStop) {
                                            try {
                                                this.rec = this.createRecognition();
                                                this.rec?.start();
                                            } catch (err) {}
                                        }
                                    }, 100);
                                }
                            };

                            return rec;
                        },

                        stopListening() {
                            this.manualStop = true;
                            this.listening = false;
                            clearTimeout(this.restartTimer);
                            this.restartTimer = null;
                            this.interimText = '';

                            if (this.rec) {
                                this.rec.onresult = null;
                                this.rec.onend = null;
                                this.rec.onerror = null;
                                try {
                                    this.rec.abort();
                                } catch (e) {}
                                this.rec = null;
                            }

                            // Pastikan input sinkron dengan teks akhir yang terverifikasi
                            if (this.committedText) {
                                this.applyText(this.committedText);
                            }
                        },

                        toggle() {
                            if (!this.supported) return;
                            if (this.listening) {
                                this.stopListening();
                                return;
                            }

                            // Matikan rekaman di kriteria lain jika ada yang aktif
                            window.dispatchEvent(new CustomEvent('voice-started', { detail: { id: '{{ $criterion->id }}' } }));

                            this.manualStop = false;
                            this.listening = true;
                            this.committedText = this.$refs.notesInput.value.trim();
                            this.interimText = '';

                            try {
                                this.rec = this.createRecognition();
                                this.rec?.start();
                            } catch (e) {
                                this.stopListening();
                            }
                        },

                        clearNotes() {
                            this.committedText = '';
                            this.interimText = '';
                            this.applyText('');
                        },
                    }"
                    @voice-started.window="if (listening && $event.detail.id !== '{{ $criterion->id }}') stopListening()">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <x-input-label :for="'notes-' . $criterion->id" :value="__('Catatan')" class="!mb-0" />
                                <button type="button" x-show="supported" @click="toggle()"
                                    :class="listening ? 'bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-400 ring-2 ring-red-500 animate-pulse' :
                                        'bg-gray-100 text-gray-600 hover:text-red-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:text-red-400'"
                                    class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold transition"
                                    :title="listening ? '{{ __('Hentikan input suara') }}' : '{{ __('Isi catatan dengan suara') }}'"
                                    :aria-label="listening ? '{{ __('Berhenti merekam') }}' : '{{ __('Isi catatan dengan suara') }}'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 15a3 3 0 003-3V6a3 3 0 10-6 0v6a3 3 0 003 3z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 11a7 7 0 01-14 0M12 18v3" />
                                    </svg>
                                    <span x-text="listening ? '{{ __('Merekam...') }}' : '{{ __('Suara') }}'"></span>
                                </button>
                            </div>
                            <button type="button"
                                @click="clearNotes()"
                                class="text-xs font-medium text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition">
                                {{ __('Kosongkan') }}
                            </button>
                        </div>

                        <div>
                            <textarea wire:model="notes.{{ $criterion->id }}" x-ref="notesInput" id="notes-{{ $criterion->id }}" rows="2"
                                autocomplete="off"
                                placeholder="{{ __('Ketik catatan atau gunakan tombol suara di atas...') }}"
                                class="block w-full border-gray-300 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-red-500 focus:ring-red-500 text-sm"></textarea>

                            {{-- Notifikasi / Indikator Voice Sedang Berjalan --}}
                            <div x-show="listening" x-transition.opacity.duration.200ms
                                class="mt-2 flex items-center justify-between gap-3 px-3 py-2 rounded-xl bg-red-500/10 dark:bg-red-950/40 border border-red-300 dark:border-red-800/80 text-red-700 dark:text-red-300 shadow-sm">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="relative flex h-3 w-3 shrink-0">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-red-600"></span>
                                    </span>
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="text-xs font-semibold truncate">{{ __('Merekam suara...') }}</span>
                                        <span class="hidden sm:inline-flex items-center gap-0.5 ml-1">
                                            <span class="w-1 h-3 bg-red-500 rounded-full animate-bounce [animation-delay:-0.3s]"></span>
                                            <span class="w-1 h-4 bg-red-500 rounded-full animate-bounce [animation-delay:-0.15s]"></span>
                                            <span class="w-1 h-2.5 bg-red-500 rounded-full animate-bounce"></span>
                                        </span>
                                        <span class="text-[11px] text-red-600/80 dark:text-red-400/80 hidden md:inline truncate">&ndash; {{ __('Bicaralah sekarang, teks akan otomatis terisi') }}</span>
                                    </div>
                                </div>

                                <button type="button" @click="stopListening()"
                                    class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-red-600 hover:bg-red-700 text-white shadow transition">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span>{{ __('Selesai') }}</span>
                                </button>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('notes.' . $criterion->id)" class="mt-2" />
                    </div>
                </div>
            @endforeach

            <div class="h-20 sm:h-16" aria-hidden="true"></div>

            <div
                class="fixed inset-x-0 z-40 px-4 py-3 border-t border-gray-200 bottom-16 sm:bottom-0 bg-white/95 dark:bg-gray-800/95 backdrop-blur dark:border-gray-700 sm:px-6 lg:px-8">
                <div class="flex justify-end mx-auto max-w-7xl">
                    <x-primary-button type="submit" class="w-full sm:w-auto" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="review">{{ __('Simpan') }}</span>
                        <span wire:loading wire:target="review" class="inline-flex items-center gap-1.5">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ __('Memeriksa...') }}
                        </span>
                    </x-primary-button>
                </div>
            </div>
        </form>

        <x-simple-modal :show="$showConfirm" show-property="showConfirm">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Konfirmasi Nilai') }}
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $registration->displayName() }}</p>

            <div class="mt-4 space-y-3">
                @foreach ($criteria as $criterion)
                    <div
                        class="flex items-start justify-between gap-4 pt-3 border-t border-gray-100 dark:border-gray-700 first:border-t-0 first:pt-0">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $criterion->name }}</p>
                            @if ($criterion->items->isNotEmpty())
                                <div class="mt-1 space-y-0.5 pl-2 border-l-2 border-red-200 dark:border-red-900/60">
                                    @foreach ($criterion->items as $item)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center justify-between gap-4">
                                            <span>{{ $item->name }}:</span>
                                            <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $itemScores[$criterion->id][$item->id] ?? '-' }}</span>
                                        </p>
                                    @endforeach
                                </div>
                            @endif
                            @if (filled($notes[$criterion->id] ?? null))
                                <p class="text-xs italic text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $notes[$criterion->id] }}</p>
                            @endif
                        </div>
                        <p class="text-lg font-bold text-red-600 shrink-0 dark:text-red-400">
                            {{ $scores[$criterion->id] ?? '-' }}</p>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <x-secondary-button type="button" wire:click="cancelReview"
                    wire:loading.attr="disabled">{{ __('Kembali') }}</x-secondary-button>
                <x-primary-button type="button" wire:click="save" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">{{ __('Konfirmasi & Simpan') }}</span>
                    <span wire:loading.flex wire:target="save" class="inline-flex items-center gap-1.5">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        {{ __('Menyimpan...') }}
                    </span>
                </x-primary-button>
            </div>
        </x-simple-modal>
    @endif
</div>
