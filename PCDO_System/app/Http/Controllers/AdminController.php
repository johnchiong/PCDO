<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    /**
     * Show admin dashboard with users and recent sync logs.
     */
    public function index(Request $request)
    {
        // Fetch users with their roles
        $users = User::with('roles:id,name')
            ->select('id', 'name', 'email', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->pluck('name')->first() ?? '—',
                    'created_at' => $user->created_at,
                ];
            });

        // Fetch total users
        $totalUsers = User::count();

        // Fetch available roles from Spatie
        $roles = Role::select('id', 'name')->orderBy('name')->get();

        // Recent sync logs
        $recentLogs = DB::table('sync_logs')
            ->select('id', 'table_name', 'action', 'record_id', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('admin/Dashboard', [
            'users' => $users,
            'totalUsers' => $totalUsers,
            'roles' => $roles,
            'recentLogs' => $recentLogs,
        ]);
    }

    /**
     * Create new user and assign role.
     */
    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users','email')],
            'role' => ['required','exists:roles,name'],
            'password' => ['required','string','min:6'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Assign role using Spatie
        $user->assignRole($data['role']);

        return redirect()->route('admin.dashboard')->with('success', 'User created successfully.');
    }
}
