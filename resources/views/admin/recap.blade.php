<x-app-layout>
    {{-- Trik menonaktifkan header/footer bawaan browser (URL, tanggal, judul, nomor halaman) saat cetak: margin @page dibuat 0 lalu jarak halaman diganti dengan padding body --}}
    <style>
        @media print {
            @page {
                margin: 0;
                size: auto;
            }

            body {
                margin: 0;
                padding: 1.5cm 1.2cm;
            }

            /* Tanpa ini, browser menghapus semua warna latar (badge jenjang, status,
               dsb) saat mencetak kecuali opsi "Background graphics" dicentang manual
               oleh pengguna di dialog print. */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>

    <x-slot name="header">
        <div class="flex items-center justify-between print:hidden">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Rekap Nilai') }}
            </h2>
            <a href="{{ route('admin.competitions') }}" class="text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                &larr; {{ __('Kembali ke Daftar Lomba') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12 print:py-0">
        <div class="max-w-7xl 2xl:max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 print:px-0 print:max-w-none">
            <livewire:admin.recap :competition-id="$competitionId" />
        </div>
    </div>
</x-app-layout>
