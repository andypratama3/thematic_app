<?php

/**
 * FILE: app/Http/Controllers/PermissionController.php
 */

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get permissions dengan user relationship
        $permissions = Permission::latest()
            ->paginate(10);

        $columns = [
            [
                'key' => 'permissionName',
                'label' => 'Permission Name',
                'type' => 'text',
            ],
            [
                'key' => 'guard_name',
                'label' => 'Guard Name',
                'type' => 'tag',
            ],
            [
                'key' => 'createdAt',
                'label' => 'Created At',
                'type' => 'date',
            ],
        ];

        // Format data untuk table component
        $permissionsData = $permissions->map(function ($permission) {
            return [
                'id' => $permission->id,
                'permissionName' => $permission->name,
                'guard_name' => $permission->guard_name ?? 'web',
                'createdAt' => $permission->created_at?->format('d M Y') ?? '-',
                'actions' => [
                    'edit' => route('permissions.edit', $permission->id),
                    'delete' => route('permissions.destroy', $permission->id),
                ]
            ];
        })->toArray();


        return view('pages.permissions.index', [
            'permissions' => $permissions,
            'permissionsData' => $permissionsData,
            'columns' => $columns,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions',
            'guard_name' => 'required|string|max:255',
        ], [
            'name.required' => 'Nama permission harus diisi',
            'name.unique' => 'Nama permission sudah ada',
            'guard_name.required' => 'Guard name harus diisi',
        ]);

        Permission::create($validated);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        return view('pages.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        return view('pages.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'guard_name' => 'required|string|max:255',
        ]);

        $permission->update($validated);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', 'Permission berhasil dihapus');
    }
}
