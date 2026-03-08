<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
   
    /*
    |--------------------------------------------------------------------------
    | LISTE UTILISATEURS
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = User::with('roles');

        //  Recherche
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%');
            });
        }

        //  Filtre rôle
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        //  Filtre statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(15);
        $roles = Role::all();

        return view('admin.users.index', compact('users','roles'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|min:6|confirmed',
            'role'     => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => $request->password, // cast hashed auto
            'status'   => 'active',
        ]);

        $user->assignRole($request->role);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user','roles'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:6|confirmed',
            'role'  => 'required|exists:roles,name',
        ]);

        $data = $request->only('name','email','phone');

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        $user->syncRoles([$request->role]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Utilisateur mis à jour.');
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVER / DESACTIVER
    |--------------------------------------------------------------------------
    */
    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas modifier votre propre statut.');
        }

        $user->isActive() ? $user->block() : $user->activate();

        return back()->with('success', 'Statut modifié.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE (Soft Delete)
    |--------------------------------------------------------------------------
    */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        if ($user->hasRole('Super Admin')) {
            return back()->with('error', 'Impossible de supprimer un Super Admin.');
        }

        if (!$user->canBeDeleted()) {
            return back()->with('error', 'Impossible de supprimer : caisse ouverte.');
        }

        $user->delete();

        return back()->with('success', 'Utilisateur supprimé.');
    }

    /*
    |--------------------------------------------------------------------------
    | RESTORE
    |--------------------------------------------------------------------------
    */
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return back()->with('success', 'Utilisateur restauré.');
    }
}