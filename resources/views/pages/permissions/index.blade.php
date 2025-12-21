{{-- FILE: resources/views/pages/permissions/index.blade.php --}}

@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Permission" />

<div class="space-y-6 sm:space-y-7">
    {{-- Alert Messages --}}
    @if ($message = Session::get('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 dark:bg-green-500/15 dark:border-green-500/30 p-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-green-600 dark:text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="text-green-700 dark:text-green-400 text-sm font-medium">{{ $message }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 dark:bg-red-500/15 dark:border-red-500/30 p-4">
            <ul class="space-y-2">
                @foreach ($errors->all() as $error)
                    <li class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-red-700 dark:text-red-400 text-sm font-medium">{{ $error }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Table Card --}}
    <x-common.component-card
        title="Permission List"
        desc="Manage all permissions in your system"
        link="{{ route('permissions.create') }}">

        <x-table.table-component
            :data="$permissionsData"
            :columns="$columns"
            :searchable="true"
            :filterable="false" />
    </x-common.component-card>

    {{-- Pagination --}}
    @if($permissions->hasPages())
        <div class="flex justify-start gap-2 ">
            {{ $permissions->links() }}
        </div>
    @endif
</div>

@endsection
