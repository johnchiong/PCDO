<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');

        $users = User::with('roles:id,name')
            ->select('id', 'name', 'email', 'created_at')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString()
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name'),
                    'created_at' => $user->created_at,
                ];
            });

        $totalUsers = User::count();

        $roles = Role::select('id', 'name')->orderBy('name')->get();

        $recentLogs = DB::table('sync_logs')
            ->select('id', 'table_name', 'user_id', 'operation', 'record_id', 'changes', 'executed_at as created_at')
            ->orderBy('executed_at', 'desc')
            ->paginate(10);

        return Inertia::render('admin/Dashboard', [
            'users' => $users,
            'totalUsers' => $totalUsers,
            'roles' => $roles,
            'recentLogs' => $recentLogs,
            'filters' => ['search' => $search],
        ]);
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', 'exists:roles,name'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['role']);

        return back()->with('success', 'User created successfully.');
    }

    public function destroyUser(User $user)
    {
        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }
}
