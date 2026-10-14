# Sub-Criteria (Rubric) Scoring

Fitur penilaian berbasis sub-item kriteria (rubrik dinamis) dirancang opsional per kriteria lomba.

## 1. Skema Database

- **`criterion_items`**: Menyimpan daftar sub-item milik kriteria (relasi `Criterion -> hasMany -> CriterionItem`).
  - Kolom: `id` (UUID), `criterion_id` (FK UUID), `name` (string), `min_score` (int), `max_score` (int), `order` (int).
- **`evaluation_items`**: Menyimpan nilai tiap sub-item dari juri (relasi `Evaluation -> hasMany -> EvaluationItem`).
  - Kolom: `id` (UUID), `evaluation_id` (FK UUID), `criterion_item_id` (FK UUID), `score` (int).

## 2. Cara Kerja

1. **Pengaturan Admin**:
   - Saat menambah/mengedit kriteria di Kelola Lomba, centang **Gunakan Sub-Item Penilaian (Rubrik)**.
   - Admin memasukkan daftar item beserta batas nilai minimal dan maksimalnya.
   - Nilai minimal dan maksimal kriteria induk secara otomatis terakumulasi dari total sub-item.
2. **Pengisian oleh Juri**:
   - Jika kriteria memiliki sub-item, form penilaian menampilkan input per sub-item.
   - Total nilai kriteria induk otomatis terhitung secara realtime dari penjumlahan nilai sub-item.
   - Saat disimpan, nilai sub-item dicatat di `evaluation_items` dan nilai total tersimpan di `evaluations.score`.
3. **Kompatibilitas Sistem**:
   - Karena total nilai tetap disimpan di `evaluations.score`, seluruh fitur lain (Rekap Nilai, Live Rank, Berita Acara, PDF) berjalan normal tanpa perubahan query kompleks.
   - Rincian nilai per item dapat dilihat pada modal Detail Juri di halaman Rekap Admin.
