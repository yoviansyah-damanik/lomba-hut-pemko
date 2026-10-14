<div class="space-y-6 animate-pulse">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @for ($i = 0; $i < 4; $i++)
            <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-4 space-y-3">
                <div class="h-9 w-9 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                <div class="h-6 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
            </div>
        @endfor
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-5 space-y-4">
        <div class="h-5 w-48 bg-gray-200 dark:bg-gray-700 rounded"></div>
        <div class="h-32 w-full bg-gray-100 dark:bg-gray-700/50 rounded-xl"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-5 space-y-4">
            <div class="h-5 w-40 bg-gray-200 dark:bg-gray-700 rounded"></div>
            <div class="space-y-3">
                <div class="h-8 bg-gray-100 dark:bg-gray-700/50 rounded"></div>
                <div class="h-8 bg-gray-100 dark:bg-gray-700/50 rounded"></div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 rounded-2xl p-5 space-y-4">
            <div class="h-5 w-40 bg-gray-200 dark:bg-gray-700 rounded"></div>
            <div class="space-y-3">
                <div class="h-8 bg-gray-100 dark:bg-gray-700/50 rounded"></div>
                <div class="h-8 bg-gray-100 dark:bg-gray-700/50 rounded"></div>
            </div>
        </div>
    </div>
</div>
