<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable(['competition_id', 'participant_id', 'school_type', 'npp', 'label', 'tie_break_order', 'score_addition', 'score_deduction', 'adjustment_notes'])]
class Registration extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'tie_break_order' => 'integer',
            'score_addition' => 'integer',
            'score_deduction' => 'integer',
        ];
    }

    /** @return BelongsTo<Competition, $this> */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /** @return BelongsTo<Participant, $this> */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    /** @return HasMany<Evaluation, $this> */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function displayName(): string
    {
        return $this->participant?->school_name ?? (string) $this->label;
    }

    /**
     * Hitung nilai akhir dari akumulasi murni juri ditambah/dikurang penyesuaian.
     */
    public function calculateFinalScore(?int $rawScore = null): int
    {
        $raw = $rawScore ?? ($this->raw_score ?? $this->total_score ?? 0);

        return max(0, (int) $raw + ($this->score_addition ?? 0) - ($this->score_deduction ?? 0));
    }

    /**
     * Urutkan registrasi untuk peringkat: total/final nilai desc, lalu urutan tie-break
     * manual dari admin (jika ada), lalu alfabetis sebagai fallback deterministik.
     * Dipakai bersama oleh Live Rank & Rekap agar urutannya selalu konsisten.
     *
     * @param  Collection<int, self>  $registrations  Sudah dimuat dengan total_score atau final_score.
     * @return Collection<int, self>
     */
    public static function sortForRanking(Collection $registrations): Collection
    {
        return $registrations
            ->sortBy([
                fn (self $a, self $b) => ($b->final_score ?? $b->total_score ?? 0) <=> ($a->final_score ?? $a->total_score ?? 0),
                fn (self $a, self $b) => ($a->tie_break_order ?? PHP_INT_MAX) <=> ($b->tie_break_order ?? PHP_INT_MAX),
                fn (self $a, self $b) => $a->displayName() <=> $b->displayName(),
            ])
            ->values();
    }
}
