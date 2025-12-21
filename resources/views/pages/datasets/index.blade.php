@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Data Set" />

<div class="min-h-screen rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-white/[0.03] xl:px-10 xl:py-12">
    <div class="mx-auto w-full">
        {{-- Header Section --}}
        <div class="mb-10">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mb-8">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white mb-2">
                        Dataset Builder
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 text-sm sm:text-base">
                        Kelola dan import dataset GIS Anda dengan mudah
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <a href="{{ route('datasets.template') }}"
                       class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 dark:text-white rounded-lg font-medium transition-all duration-300 flex items-center justify-center sm:justify-start gap-2 shadow-lg hover:shadow-green-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Download Template</span>
                    </a>
                    <button onclick="document.getElementById('importModal').classList.remove('hidden')"
                            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 dark:text-white rounded-lg font-medium transition-all duration-300 flex items-center justify-center sm:justify-start gap-2 shadow-lg hover:shadow-blue-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Tambah Dataset</span>
                    </button>
                </div>
            </div>

            {{-- Search & Filter Section --}}
            <div class="bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 relative">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text"
                               id="searchInput"
                               placeholder="Cari dataset..."
                               class="w-full pl-12 pr-4 py-2.5 rounded-lg bg-gray-50 dark:bg-white/5 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                    </div>
                    <select id="typeFilter"
                            class="px-4 py-2.5 rounded-lg bg-gray-50 dark:bg-white/5 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 min-w-[150px]">
                        <option value="">Semua Tipe</option>
                        <option value="fertilizer">Pupuk</option>
                        <option value="farmer">Petani</option>
                        <option value="transaction">Transaksi</option>
                        <option value="custom">Kustom</option>
                    </select>
                    <select id="statusFilter"
                            class="px-4 py-2.5 rounded-lg bg-gray-50 dark:bg-white/5 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 min-w-[150px]">
                        <option value="">Semua Status</option>
                        <option value="completed">Selesai</option>
                        <option value="processing">Memproses</option>
                        <option value="failed">Gagal</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Datasets Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($datasets as $dataset)
            <div class="group bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden hover:shadow-xl dark:hover:shadow-2xl transition-all duration-300 hover:border-gray-300 dark:hover:border-gray-700"
                 data-dataset-name="{{ strtolower($dataset->name) }}"
                 data-dataset-type="{{ $dataset->type }}"
                 data-dataset-status="{{ $dataset->import_status }}">
                {{-- Card Header --}}
                <div class="p-6 pb-4">
                    <div class="flex items-start justify-between mb-5">
                        <div class="flex items-center gap-4 flex-1">
                            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 via-blue-600 to-purple-600 rounded-lg flex items-center justify-center shadow-lg flex-shrink-0">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-gray-900 dark:text-white font-semibold text-lg truncate">{{ $dataset->name }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $dataset->slug }}</p>
                            </div>
                        </div>
                        <button class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Description --}}
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-5 line-clamp-2">
                        {{ $dataset->description ?? 'Tidak ada deskripsi' }}
                    </p>

                    {{-- Stats Grid --}}
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-lg p-3 text-center">
                            <div class="text-xs font-medium text-blue-600 dark:text-blue-400 mb-1">Total Record</div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($dataset->total_records ?? 0) }}</div>
                        </div>
                        <div class="bg-purple-50 dark:bg-purple-500/10 border border-purple-200 dark:border-purple-500/20 rounded-lg p-3 text-center">
                            <div class="text-xs font-medium text-purple-600 dark:text-purple-400 mb-1">Parameter</div>
                            <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $dataset->total_parameters ?? 0 }}</div>
                        </div>
                    </div>

                    {{-- Status & Date --}}
                    <div class="flex items-center justify-between mb-5 pb-5 border-b border-gray-200 dark:border-gray-800">
                        <div>
                            @if($dataset->import_status === 'completed')
                                <span class="inline-flex items-center gap-2 px-3 py-1 bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 text-xs font-semibold rounded-full border border-green-200 dark:border-green-500/20">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Selesai
                                </span>
                            @elseif($dataset->import_status === 'processing')
                                <span class="inline-flex items-center gap-2 px-3 py-1 bg-yellow-50 dark:bg-yellow-500/10 text-yellow-700 dark:text-yellow-400 text-xs font-semibold rounded-full border border-yellow-200 dark:border-yellow-500/20">
                                    <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Memproses
                                </span>
                            @else
                                <span class="inline-flex items-center gap-2 px-3 py-1 bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 text-xs font-semibold rounded-full border border-red-200 dark:border-red-500/20">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                    Gagal
                                </span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                            {{ $dataset->imported_at ? $dataset->imported_at->diffForHumans() : 'Belum diimpor' }}
                        </div>
                    </div>
                </div>

                {{-- Card Actions --}}
                <div class="px-6 pb-6 flex flex-col gap-2">
                    <a href="{{ route('maps.index') }}?dataset={{ $dataset->id }}"
                       class="w-full px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white text-sm font-medium rounded-lg transition-all duration-300 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Lihat Peta
                    </a>
                    <div class="flex gap-2">
                        <button class="flex-1 px-4 py-2 bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                            OCR
                        </button>
                        <form action="{{ route('datasets.destroy', $dataset) }}" method="POST" class="flex-1">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus dataset ini?')"
                                    class="w-full px-4 py-2 bg-red-100 dark:bg-red-500/10 hover:bg-red-200 dark:hover:bg-red-500/20 text-red-700 dark:text-red-400 text-sm font-medium rounded-lg transition-colors">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-12">
            {{ $datasets->links() }}
        </div>
    </div>
</div>

{{-- Import Modal --}}
<div id="importModal" class="hidden fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999">
    <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]" onclick="document.getElementById('importModal').classList.add('hidden')"></div>

    <div class="relative w-full max-w-[584px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">
        {{-- Close Button --}}
        <button onclick="document.getElementById('importModal').classList.add('hidden')" class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-gray-200 sm:right-6 sm:top-6 sm:h-11 sm:w-11">
            <svg class="transition-colors fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill=""></path>
            </svg>
        </button>

        <form action="{{ route('datasets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">
                Import Dataset Baru
            </h4>

            <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                {{-- Dataset Name --}}
                <div class="col-span-1 sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Nama Dataset <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required placeholder="Penyaluran Pupuk Bersubsidi" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800">
                </div>

                {{-- Description --}}
                <div class="col-span-1 sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Deskripsi
                    </label>
                    <textarea name="description" rows="3" placeholder="Jelaskan isi dan tujuan dataset ini..." class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800 resize-none"></textarea>
                </div>

                {{-- Type --}}
                <div class="col-span-1">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Tipe Dataset <span class="text-red-500">*</span>
                    </label>
                    <select name="type" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800">
                        <option value="">Pilih Tipe</option>
                        <option value="fertilizer">Pupuk</option>
                        <option value="farmer">Petani</option>
                        <option value="transaction">Transaksi</option>
                        <option value="custom">Kustom</option>
                    </select>
                </div>

                {{-- Year --}}
                <div class="col-span-1">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Tahun <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="year" value="{{ date('Y') }}" min="2000" max="2100" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800">
                </div>

                {{-- Month --}}
                <div class="col-span-1">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Bulan <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="month" value="{{ date('m') }}" min="1" max="12" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800">
                </div>

                {{-- File Upload --}}
                <div class="col-span-1 sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Upload File <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" required class="hidden" onchange="handleFileSelect(this)">
                        <label for="fileInput" class="flex flex-col items-center justify-center w-full px-6 py-8 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl hover:border-blue-500 dark:hover:border-blue-500 transition-colors cursor-pointer hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <div class="text-center">
                                <svg class="mx-auto h-10 w-10 text-gray-400 dark:text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-gray-700 dark:text-gray-300 text-sm font-medium mb-0.5">
                                    <span class="text-blue-600 dark:text-blue-400">Klik untuk unggah</span> atau seret file
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    XLSX, XLS, CSV (maks 10MB)
                                </p>
                                <p id="fileName" class="text-xs text-gray-500 dark:text-gray-500 mt-1 font-medium">
                                    File belum dipilih
                                </p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end w-full gap-3 mt-6">
                <button onclick="document.getElementById('importModal').classList.add('hidden')" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">
                    Batal
                </button>
                <button type="submit" class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-blue-600 shadow-theme-xs hover:bg-blue-700 sm:w-auto transition-colors">
                    Import Dataset
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // File upload handling
    function handleFileSelect(input) {
        const fileName = input.files[0]?.name || 'File belum dipilih';
        const fileSize = input.files[0]?.size || 0;
        const sizeMB = (fileSize / (1024 * 1024)).toFixed(2);

        const fileNameElement = document.getElementById('fileName');
        if (input.files[0]) {
            fileNameElement.textContent = `${fileName} (${sizeMB} MB)`;
        } else {
            fileNameElement.textContent = 'File belum dipilih';
        }
    }

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('[data-dataset-name]').forEach(card => {
            const name = card.getAttribute('data-dataset-name').toLowerCase();
            card.style.display = name.includes(searchTerm) ? '' : 'none';
        });
    });

    // Filter functionality
    document.getElementById('typeFilter').addEventListener('change', function(e) {
        const type = e.target.value;
        document.querySelectorAll('[data-dataset-type]').forEach(card => {
            card.style.display = !type || card.getAttribute('data-dataset-type') === type ? '' : 'none';
        });
    });

    document.getElementById('statusFilter').addEventListener('change', function(e) {
        const status = e.target.value;
        document.querySelectorAll('[data-dataset-status]').forEach(card => {
            card.style.display = !status || card.getAttribute('data-dataset-status') === status ? '' : 'none';
        });
    });

    // Modal close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.getElementById('importModal').classList.add('hidden');
        }
    });

    // Close modal when clicking backdrop
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('importModal');
        if (e.target.classList.contains('modal-close-btn')) {
            modal.classList.add('hidden');
        }
    });
</script>

@endsection
