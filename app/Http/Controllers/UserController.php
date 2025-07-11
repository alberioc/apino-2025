<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CompanyGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('companyGroup')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function create()
    {
        $companyGroups = CompanyGroup::withCount('pagantes')
            ->whereHas('pagantes')
            ->get();

        $roles = ['user' => 'Usuário', 'admin' => 'Administrador', 'gerente' => 'Gerente'];

        return view('users.create', compact('companyGroups', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'company_group' => 'required|exists:company_groups,id',
            'role' => 'required|in:user,admin,gerente',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_group' => $request->company_group,
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function edit(User $user)
    {
        $companyGroups = CompanyGroup::withCount('pagantes')
            ->whereHas('pagantes')
            ->get();

        $roles = ['user' => 'Usuário', 'admin' => 'Administrador', 'gerente' => 'Gerente'];

        return view('users.edit', compact('user', 'companyGroups', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'company_group' => 'required|exists:company_groups,id',
            'role' => 'required|in:user,admin,gerente',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->company_group = $request->company_group;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuário excluído com sucesso!');
    }
}
