@extends('layouts.app')

@section('content')
<div class="min-h-screen ">
    <div class="container mx-auto px-4 py-8">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Dataset Builder</h1>
                <p class="text-gray-400 mt-2">Kelola dan import dataset GIS Anda</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('datasets.template') }}"
                   class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download Template
                </a>
                <button onclick="document.getElementById('importModal').classList.remove('hidden')"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New Dataset
                </button>
            </div>
        </div>

        {{-- Search & Filter --}}
        <div class=" rounded-lg p-4 mb-6">
            <div class="flex gap-4">
                <input type="text"
                       placeholder="Search datasets..."
                       class="flex-1  text-white px-4 py-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none border border-gray-600">
                <select class=" text-white px-4 py-2 rounded-lg border border-gray-600 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option>All Types</option>
                    <option>Fertilizer</option>
                    <option>Farmer</option>
                    <option>Transaction</option>
                </select>
                <select class=" text-white px-4 py-2 rounded-lg border border-gray-600 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option>All Status</option>
                    <option>Completed</option>
                    <option>Processing</option>
                    <option>Failed</option>
                </select>
            </div>
        </div>

        {{-- Datasets Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($datasets as $dataset)
            <div class=" rounded-lg overflow-hidden hover:shadow-xl transition border border-gray-700">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold">{{ $dataset->name }}</h3>
                                <span class="text-xs text-gray-400">{{ $dataset->slug }}</span>
                            </div>
                        </div>
                        <div class="relative">
                            <button class="text-gray-400 hover:text-white">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <p class="text-gray-400 text-sm mb-4">{{ Str::limit($dataset->description, 80) }}</p>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class=" rounded p-3">
                            <div class="text-xs text-gray-400">Total Records</div>
                            <div class="text-xl font-bold text-white">{{ number_format($dataset->total_records) }}</div>
                        </div>
                        <div class=" rounded p-3">
                            <div class="text-xs text-gray-400">Parameters</div>
                            <div class="text-xl font-bold text-white">{{ $dataset->total_parameters }}</div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            @if($dataset->import_status === 'completed')
                                <span class="px-3 py-1 bg-green-900/50 text-green-400 text-xs rounded-full">Completed</span>
                            @elseif($dataset->import_status === 'processing')
                                <span class="px-3 py-1 bg-yellow-900/50 text-yellow-400 text-xs rounded-full">Processing</span>
                            @else
                                <span class="px-3 py-1 bg-red-900/50 text-red-400 text-xs rounded-full">Failed</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $dataset->imported_at ? $dataset->imported_at->diffForHumans() : 'Not imported' }}
                        </div>
                    </div>

                    <div class="flex gap-2 mt-4 pt-4 border-t border-gray-700">
                        <a href="{{ route('maps.index') }}?dataset={{ $dataset->id }}"
                           class="flex-1 text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded transition">
                            View Map
                        </a>
                        <button class="px-4 py-2  hover: text-white text-sm rounded transition">
                            OCR
                        </button>
                        <form action="{{ route('datasets.destroy', $dataset) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('Are you sure?')"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded transition">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $datasets->links() }}
        </div>
    </div>
</div>

{{-- Import Modal - TailAdmin Style --}}
<div id="importModal" class="hidden fixed left-0 top-0 z-999999 flex h-full min-h-screen w-full items-center justify-center bg-black/90 px-4 py-5">
    <div class="w-full max-w-2xl rounded-lg bg-white dark: px-8 py-12 md:py-15 md:px-17.5 relative">
        {{-- Close Button --}}
        <button onclick="document.getElementById('importModal').classList.add('hidden')"
                class="absolute top-6 right-6 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.8913 9.99599L19.5043 2.38635C20.032 1.85888 20.032 1.02306 19.5043 0.495589C18.9768 -0.0317329 18.141 -0.0317329 17.6135 0.495589L10.0001 8.10559L2.38673 0.495589C1.85917 -0.0317329 1.02343 -0.0317329 0.495873 0.495589C-0.0318274 1.02306 -0.0318274 1.85888 0.495873 2.38635L8.10887 9.99599L0.495873 17.6056C-0.0318274 18.1331 -0.0318274 18.9689 0.495873 19.4964C0.717307 19.7177 1.05898 19.8864 1.4413 19.8864C1.75372 19.8864 2.13282 19.7971 2.40606 19.4771L10.0001 11.8864L17.6135 19.4964C17.8349 19.7177 18.1766 19.8864 18.5589 19.8864C18.8724 19.8864 19.2531 19.7964 19.5265 19.4737C20.0319 18.9452 20.0245 18.1256 19.5043 17.6056L11.8913 9.99599Z" fill="currentColor"/>
            </svg>
        </button>

        {{-- Modal Header --}}
        <div class="mb-8">
            <h3 class="pb-2 text-2xl font-bold text-black dark:text-white sm:text-3xl">
                Import New Dataset
            </h3>
            <span class="mx-auto inline-block h-1 w-22.5 rounded bg-primary"></span>
        </div>

        {{-- Modal Content --}}
        <form action="{{ route('datasets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="space-y-6">
                {{-- Dataset Name --}}
                <div>
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                        Dataset Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required
                           placeholder="e.g., Penyaluran Pupuk Bersubsidi"
                           class="w-full rounded-lg border border-stroke bg-transparent py-3 px-5 text-black outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary">
                </div>

                {{-- Description --}}
                <div>
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                        Description
                    </label>
                    <textarea name="description" rows="3"
                              placeholder="Dataset description..."
                              class="w-full rounded-lg border border-stroke bg-transparent py-3 px-5 text-black outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary"></textarea>
                </div>

                {{-- Type and Date --}}
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                            Type <span class="text-red-500">*</span>
                        </label>
                        <select name="type" required
                                class="w-full rounded-lg border border-stroke bg-transparent py-3 px-5 text-black outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary">
                            <option value="fertilizer">Fertilizer</option>
                            <option value="farmer">Farmer</option>
                            <option value="transaction">Transaction</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                                Year <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="year" value="{{ date('Y') }}" min="2000" max="2100" required
                                   class="w-full rounded-lg border border-stroke bg-transparent py-3 px-5 text-black outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary">
                        </div>
                        <div>
                            <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                                Month <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="month" value="{{ date('m') }}" min="1" max="12" required
                                   class="w-full rounded-lg border border-stroke bg-transparent py-3 px-5 text-black outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary">
                        </div>
                    </div>
                </div>

                {{-- File Upload --}}
                <div>
                    <label class="mb-3 block text-sm font-medium text-black dark:text-white">
                        Upload File <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                               class="w-full cursor-pointer rounded-lg border-2 border-dashed border-stroke bg-transparent outline-none transition file:mr-5 file:border-collapse file:cursor-pointer file:border-0 file:border-r file:border-solid file:border-stroke file:bg-whiter file:py-3 file:px-5 file:hover:bg-primary file:hover:bg-opacity-10 focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:file:border-form-strokedark dark:file:bg-white/30 dark:file:text-white dark:focus:border-primary">
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                            <div class="flex flex-col items-center">
                                <svg class="fill-current mb-2.5" width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M33.75 27.5V32.5C33.75 33.163 33.4866 33.7989 33.0178 34.2678C32.5489 34.7366 31.913 35 31.25 35H8.75C8.08696 35 7.45107 34.7366 6.98223 34.2678C6.51339 33.7989 6.25 33.163 6.25 32.5V27.5" stroke="#4F5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M11.25 16.25L20 7.5L28.75 16.25" stroke="#4F5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M20 7.5V27.5" stroke="#4F5E77" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    <span class="text-primary">Click to upload</span> or drag and drop
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">XLSX, XLS, CSV (max 10MB)</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-4 pt-2">
                    <button type="submit"
                            class="flex-1 inline-flex justify-center items-center rounded-lg bg-primary py-3 px-10 text-center font-medium text-white hover:bg-opacity-90 transition">
                        Import Dataset
                    </button>
                    <button type="button"
                            onclick="document.getElementById('importModal').classList.add('hidden')"
                            class="inline-flex justify-center items-center rounded-lg border border-stroke py-3 px-10 text-center font-medium text-black hover:shadow-1 dark:border-strokedark dark:text-white transition">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
