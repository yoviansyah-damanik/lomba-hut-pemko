# Rencana Penyesuaian Kategori / Jenjang Dinamis per Lomba

## 1. Latar Belakang & Kebutuhan
Saat ini, kategori lomba terkunci secara statis pada jenjang sekolah: `['SD', 'SMP', 'SMA', 'Umum']` melalui kolom database bertipe `ENUM` dan konstanta di beberapa komponen Livewire.
Untuk lomba seperti **Marching Band / Drum Band**, pembagian kategori/divisi yang digunakan bukanlah jenjang sekolah standar, melainkan:
1. **Marching Band**
2. **Drum Band**

Di masa mendatang, lomba lain mungkin juga memiliki kategori khusus (misalnya: *Tingkat Pemula / Lanjutan*, *Putra / Putri*, dll).

---

## 2. Analisis Arsitektur

### Kondisi Saat Ini (As-Is)
- **Tabel Database**:
  - `competitions`: belum menyimpan daftar kategori yang berlaku untuk lomba tersebut.
  - `registrations.school_type`: `ENUM('SD', 'SMP', 'SMA', 'Umum')`
  - `criterion_user.school_type`: `ENUM('SD', 'SMP', 'SMA', 'Umum')`
  - `participants.school_type`: `ENUM('SD', 'SMP', 'SMA', 'Umum')`
  - `criteria.school_types`: `JSON`
- **Kode Program**:
  - Konstanta `SCHOOL_TYPES = ['SD', 'SMP', 'SMA', 'Umum']` di-hardcode pada `Admin\Competition`, `Admin\CompetitionParticipants`, `Admin\Recap`, dan `LiveRank\Board`.

### Target Solusi (To-Be)
- **Kolom Kategori Dinamis pada Lomba**:
  - Tabel `competitions` ditambahkan kolom `categories` (`JSON`, nullable).
  - Jika `categories` bernilai `null` atau kosong, sistem otomatis memakai default: `['SD', 'SMP', 'SMA', 'Umum']` (*100% backward compatible* dengan data lomba lama).
- **Relaksasi ENUM Database**:
  - Kolom `school_type` pada `registrations`, `criterion_user`, dan `participants` diubah dari `ENUM` menjadi `VARCHAR(50)`.
  - Hal ini memungkinkan penyimpanan nama kategori apa pun (seperti `'Marching Band'`, `'Drum Band'`, `'SD'`, `'Umum'`).
- **Pilihan Preset pada Form Tambah/Edit Lomba**:
  - Admin dapat memilih opsi preset kategori saat membuat lomba:
    1. **Jenjang Sekolah (Default)**: `SD`, `SMP`, `SMA`, `Umum`
    2. **Divisi Band**: `Marching Band`, `Drum Band`
    3. **Kustom**: Admin dapat menambah/menghapus tag kategori sendiri sesuai kebutuhan.

---

## 3. Komponen yang Akan Disesuaikan

| Modul / File | Penyesuaian yang Dilakukan |
|---|---|
| **Database Migration** | - Menambahkan kolom `categories` pada tabel `competitions`.<br>- Mengubah kolom `school_type` di tabel `registrations`, `criterion_user`, dan `participants` menjadi `VARCHAR(50)`. |
| **Model `Competition`** | - Menambahkan `categories` ke `$casts` sebagai `array`.<br>- Menambahkan accessor `getCategoriesAttribute()` yang mengembalikan default `['SD', 'SMP', 'SMA', 'Umum']` jika kosong.<br>- Konstanta preset: `PRESET_SCHOOL` & `PRESET_BAND`. |
| **Admin: Form Lomba (`Competition.php` & Blade)** | - Pilihan preset kategori di modal lomba: *Jenjang Sekolah* / *Divisi Band* / *Kustom*.<br>- Form kriteria dan penugasan juri otomatis menggunakan kategori milik lomba terkait, bukan hardcode 4 jenjang. |
| **Admin: Kelola Peserta (`CompetitionParticipants.php` & Blade)** | - Form pembuatan slot massal otomatis menampilkan input sesuai kategori lomba (misal: slot untuk Marching Band & Drum Band).<br>- Tab filter peserta mengikuti kategori lomba. |
| **Admin: Rekap Nilai (`Admin\Recap.php` & Blade)** | - Tab filter jenjang/kategori dan unduh Berita Acara dinamis mengikuti kategori lomba yang aktif. |
| **Dewan Juri: Penilaian (`Evaluation\ParticipantLookup.php` & `Recap.php`)** | - Pilihan jenjang saat mencari nomor peserta (NPP) otomatis mengambil dari kategori lomba yang sedang dinilai. |
| **Papan Publik: Live Rank (`LiveRank\Board.php` & Blade)** | - Tab kategori di papan peringkat live dinamis mengikuti kategori lomba yang dibuka. |
| **Helper Warna Badge** | - Pemetaan warna badge badge yang adaptif untuk nama kategori kustom (misal Marching Band bernuansa Indigo/Blue, Drum Band bernuansa Amber/Orange). |

---

## 4. Rencana Tahapan Eksekusi

1. **Tahap 1 — Database Migration**:
   - Buat migrasi untuk menambahkan `categories` di `competitions` dan melonggarkan `school_type` ke `VARCHAR(50)`.
2. **Tahap 2 — Model & Logika Dasar**:
   - Update model `Competition`, `Registration`, `Criterion`.
3. **Tahap 3 — Form Tambah/Edit Lomba**:
   - Tambahkan opsi pemilihan kategori (Sekolah / Divisi Band / Kustom) pada Livewire `Admin\Competition`.
4. **Tahap 4 — Penyesuaian Modul Terkait**:
   - Sesuaikan modul peserta (`CompetitionParticipants`), juri (`ParticipantLookup`), rekap admin (`Admin\Recap`), dan live rank (`LiveRank\Board`).
5. **Tahap 5 — Seeder & Test Suite**:
   - Update `DatabaseSeeder` agar `Lomba Drumband / Marching Band` langsung memakai kategori `['Marching Band', 'Drum Band']`.
   - Jalankan `php artisan test` dan pastikan seluruh test case hijau.
