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
        $logsPage = $request->query('logs_page', 1);
        $usersPage = $request->query('users_page', 1);

        $users = User::with('roles:id,name')
            ->select('id', 'name', 'email', 'created_at')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'users_page', $usersPage)
            ->withQueryString()
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->map(fn ($r) => ['id' => $r->id, 'name' => $r->name]),
                    'created_at' => $user->created_at,
                ];
            });

        $roles = Role::select('id', 'name')->orderBy('name')->get();

        $recentLogs = DB::table('sync_logs')
            ->select('id', 'table_name', 'user_id', 'operation', 'record_id', 'changes', 'executed_at as created_at')
            ->orderBy('executed_at', 'desc')
            ->paginate(10, ['*'], 'logs_page', $logsPage)
            ->withQueryString();

        return Inertia::render('admin/Dashboard', [
            'users' => $users,
            'roles' => $roles,
            'recentLogs' => $recentLogs,
            'filters' => ['search' => $search],
        ]);
    }

    public function storeUser(Request $request)
    {
        $authUser = $request->user();

        if ($authUser->hasRole('superadmin')) {
            $allowedRoles = ['admin', 'officer'];
        } elseif ($authUser->hasRole('admin')) {
            $allowedRoles = ['officer'];
        } else {
            abort(403, 'Unauthorized to create users.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in($allowedRoles)],
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

    public function destroyUser(User $user, Request $request)
    {
        $authUser = $request->user();

        $targetRoles = $user->roles->pluck('name')->toArray();

        if ($authUser->hasRole('superadmin')) {
            if (! in_array('admin', $targetRoles) && ! in_array('officer', $targetRoles)) {
                abort(403, 'Superadmin can only delete admins or officers.');
            }
        } elseif ($authUser->hasRole('admin')) {
            if (! in_array('officer', $targetRoles)) {
                abort(403, 'Admin can only delete officers.');
            }
        } else {
            abort(403, 'Unauthorized to delete users.');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }
}
