@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Permission" />
    <div class="space-y-6 sm:space-y-7">
        <x-common.component-card title="Basic Table 1">
                <x-tables.basic-tables.basic-tables-one />
            </x-common.component-card>
    </div>
</div>
@endsection
