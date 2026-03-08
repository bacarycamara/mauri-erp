<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | LISTE DES ROLES
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $roles = Role::withCount('permissions')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.roles.index', compact('roles'));
    }

    /*
    |--------------------------------------------------------------------------
    | FORM CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $permissions = Permission::orderBy('name')
            ->get()
            ->groupBy(function ($permission) {
                return explode(' ', $permission->name)[1] ?? 'other';
            });

        return view('admin.roles.create', compact('permissions'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array'
        ]);

        DB::transaction(function () use ($validated) {

            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web'
            ]);

            $role->syncPermissions($validated['permissions'] ?? []);
        });

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Rôle créé avec succès.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $role = Role::findOrFail($id);

        // Protection rôle critique
        if ($role->name === 'Super Admin') {
            abort(403, 'Modification du Super Admin interdite.');
        }

        $permissions = Permission::orderBy('name')
            ->get()
            ->groupBy(function ($permission) {
                return explode(' ', $permission->name)[1] ?? 'other';
            });

        $rolePermissions = $role->permissions
            ->pluck('name')
            ->toArray();

        return view('admin.roles.edit', compact(
            'role',
            'permissions',
            'rolePermissions'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // Protection rôle critique
        if ($role->name === 'Super Admin') {
            abort(403, 'Modification du Super Admin interdite.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array'
        ]);

        DB::transaction(function () use ($role, $validated) {

            $role->update([
                'name' => $validated['name']
            ]);

            // sync sécurisé
            $role->syncPermissions($validated['permissions'] ?? []);
        });

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Rôle modifié avec succès.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Protection rôles système
        if ($role->name === 'Super Admin') {
            return back()->with('error', 'Impossible de supprimer un rôle système.');
        }

        // Empêcher suppression si utilisé
        if ($role->users()->exists()) {
            return back()->with('error', 'Ce rôle est assigné à des utilisateurs.');
        }

        DB::transaction(function () use ($role) {
            $role->delete();
        });

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Rôle supprimé avec succès.');
    }
}