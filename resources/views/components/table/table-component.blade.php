@props([
    'data' => [],
    'columns' => [],
    'searchable' => true,
    'filterable' => true,
]);

@push('styles')
    <style>
        /* ================================
        SweetAlert2 FIX & THEME
        ================================ */

        /* FIX BUG: grid bikin tombol disabled */
        .swal2-popup {
            display: flex !important;
            flex-direction: column;
        }

        /* Pastikan tombol selalu sejajar */
        .swal2-actions {
            display: flex !important;
            gap: 0.75rem;
        }

        /* Hilangkan backdrop putih bawaan */
        div:where(.swal2-container).swal2-backdrop-show,
        div:where(.swal2-container).swal2-noanimation {
            background: transparent !important;
        }

        /* ================================
        DARK MODE
        ================================ */
        .dark .swal2-popup {
            background-color: #020617 !important; /* slate-950 */
            color: #e5e7eb !important;
            border-radius: 1rem;
        }

        .dark .swal2-title {
            color: #f9fafb !important;
        }

        .dark .swal2-html-container {
            color: #cbd5f5 !important;
        }

        /* Icon warning */
        .dark .swal2-icon.swal2-warning {
            border-color: #facc15 !important;
            color: #facc15 !important;
        }

        /* Buttons */
        .dark .swal2-confirm {
            background-color: #dc2626 !important;
            color: #fff !important;
            border-radius: 0.75rem;
        }

        .dark .swal2-confirm:hover {
            background-color: #b91c1c !important;
        }

        .dark .swal2-cancel {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
            border-radius: 0.75rem;
        }

        .dark .swal2-cancel:hover {
            background-color: #374151 !important;
        }

        /* Backdrop dark */
        .dark .swal2-backdrop-show {
            background: rgba(2, 6, 23, 0.85) !important;
        }

    </style>
@endpush

<div
    x-data="{
        items: @js($data),
        search: '',
        statusFilter: '',
        get filteredItems() {
            let result = this.items;

            if (this.search) {
                const keyword = this.search.toLowerCase();
                result = result.filter(item =>
                    Object.values(item).some(val => {
                        if (val === null || val === undefined) return false;

                        if (typeof val === 'object') {
                            return Object.values(val).some(v =>
                                String(v).toLowerCase().includes(keyword)
                            );
                        }

                        return String(val).toLowerCase().includes(keyword);
                    })
                );
            }

            if (this.statusFilter) {
                result = result.filter(item =>
                    Object.prototype.hasOwnProperty.call(item, 'status')
                        ? item.status === this.statusFilter
                        : true
                );
            }

            return result;
        },
        getStatusClass(status) {
            const classes = {
                Active: 'bg-green-50 text-green-700',
                Inactive: 'bg-gray-50 text-gray-700',
                Pending: 'bg-yellow-50 text-yellow-700',
                Processing: 'bg-blue-50 text-blue-700',
                Completed: 'bg-green-50 text-green-700',
                Failed: 'bg-red-50 text-red-700',
                Cancelled: 'bg-red-50 text-red-700',
            };
            return classes[status] || 'bg-gray-50 text-gray-700';
        }
    }"
    class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
>

    <div class="max-w-full overflow-x-auto">
        <table class="w-full min-w-[800px]">
            <thead>
                <tr class="border-b dark:text-white border-gray-100 dark:border-gray-800">
                    @foreach($columns as $column)
                        <th class="px-6 py-4 text-left dark:text-white text-sm font-medium">
                            {{ $column['label'] }}
                        </th>
                    @endforeach
                    <th class="px-6 py-4 text-center text-sm font-medium">
                        Action
                    </th>
                </tr>
            </thead>

            <tbody>
                <template x-for="item in filteredItems" :key="item.id">
                    <tr class="border-b border-gray-100 dark:border-gray-800">

                        <template x-for="column in @js($columns)" :key="column.key">
                            <td class="px-6 py-4">

                                <template x-if="column.type === 'text'">
                                    <p class="text-sm text-gray-700 dark:text-gray-400"
                                       x-text="item[column.key]"></p>
                                </template>

                                <template x-if="column.type === 'tag'">
                                    <span class="inline-block px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs"
                                          x-text="item[column.key]"></span>
                                </template>

                                <template x-if="column.type === 'date'">
                                    <p class="text-sm text-gray-600 dark:text-gray-400"
                                       x-text="item[column.key]"></p>
                                </template>

                            </td>
                        </template>

                        <td class="px-6 py-4">
                            <div x-data="{ open: false }" class="relative inline-block justify-items-center">
                                <button
                                    @click="open = !open"
                                    class="text-gray-500 hover:text-gray-700"
                                >
                                    ⋮
                                </button>

                                <div
                                    x-show="open"
                                    @click.outside="open = false"
                                    x-transition
                                    class="absolute right-0 z-50 mt-2 w-40 rounded-xl border  shadow-lg"
                                >
                                    <a
                                        :href="item.actions?.edit"
                                        class="block px-4 py-2 text-sm dark:text-white"
                                    >
                                        Edit
                                    </a>

                                   <button
                                        class="w-full text-left dark:text-white px-4 py-2 text-sm text-red-600 js-confirm-delete"
                                        :data-url="item.actions?.delete">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </td>

                    </tr>
                </template>

                {{-- Empty --}}
                <template x-if="filteredItems.length === 0">
                    <tr>
                        <td :colspan="@js(count($columns) + 1)"
                            class="py-8 text-center text-gray-500">
                            Tidak ada data
                        </td>
                    </tr>
                </template>

            </tbody>
        </table>
    </div>
</div>


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/**
 * SweetAlert Helper
 */
window.swalConfirm = function ({
    title = 'Anda yakin?',
    text = 'Data yang sudah dihapus tidak dapat dikembalikan!',
    confirmText = 'Hapus!',
    cancelText = 'Batal',
    icon = 'warning',
    onConfirm = () => {}
}) {
    return Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        reverseButtons: true,
        allowOutsideClick: false,
        allowEscapeKey: true,
        didOpen: () => {
            // force reflow agar disabled langsung dilepas
            const popup = Swal.getPopup();
            popup && popup.offsetHeight;
        }
    }).then(result => {
        if (result.isConfirmed) {
            onConfirm();
        }
    });
};
</script>
<script>
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.js-confirm-delete');
    if (!btn) return;

    e.preventDefault();

    const url = btn.dataset.url;
    if (!url) return;

    swalConfirm({
        onConfirm: () => {
            window.location.href = url;
        }
    });
});
</script>
@endpush
