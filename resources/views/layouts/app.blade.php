<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.meta')
        @include('partials.theme-init')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900 pt-16 pb-20 sm:pb-0 print:pt-0 print:pb-0 print:bg-white flex flex-col">
            <div class="print:hidden">
                <livewire:layout.navigation />
            </div>

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-100 dark:border-gray-700 print:hidden">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-1">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="mt-auto py-6 border-t border-gray-200/60 dark:border-gray-800/60 text-center text-xs text-gray-500 dark:text-gray-400 print:hidden mb-16 sm:mb-0">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }} &middot; {{ __('Bidang Humas dan Informatika Kwartir Cabang Gerakan Pramuka Kota Padangsidimpuan') }}</p>
                </div>
            </footer>
        </div>
    </body>
</html>
