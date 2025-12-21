@props([
    'title',
    'desc' => '',
    'link' => '',
])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]']) }}>
    <!-- Card Header -->
    <div class="flex items-center justify-between gap-4 px-6 py-5">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3">
                <div class="min-w-0">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">
                        {{ $title }}
                    </h3>
                    @if($desc)
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                            {{ $desc }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        @if($link)
            <div class="flex-shrink-0">
                <a href="{{ $link }}" class="inline-flex items-center gap-2 h-11 px-4 py-2.5 text-sm font-medium dark:text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-theme-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="hidden sm:inline">Tambah Data</span>
                    <span class="sm:hidden">Tambah</span>
                </a>
            </div>
        @endif
    </div>

    <!-- Card Divider -->
    <div class="border-t border-gray-100 dark:border-gray-800"></div>

    <!-- Card Body -->
    <div class="px-6 py-5 sm:p-6">
        <div class="space-y-6">
            {{ $slot }}
        </div>
    </div>
</div>
