<?php

namespace App\Http\Controllers;

use App\Constants\DateTimeConstants;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use HttpResponses;

    /**
     * Get DataTables data for users listing
     */
    public function data(Request $request)
    {
        $users = User::select(['id', 'name', 'email', 'role_id', 'status', 'created_at'])
            ->with('role:id,name');

        return DataTables::eloquent($users)
            ->editColumn('role_name', function ($data) {
                return $data->role ? $data->role->name : 'No Role';
            })
            ->editColumn('status', function ($data) {
                return $data->status === 'active' ? 'Active' : 'Inactive';
            })
            ->editColumn('created_at', function ($data) {
                return $data->created_at?->timezone(DateTimeConstants::TIMEZONE)
                    ->format(DateTimeConstants::DISPLAY_DATETIME_FORMAT);
            })
            ->only(['id', 'name', 'email', 'role_name', 'status', 'created_at'])
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('view_users')) {
            abort(403, 'Unauthorized action.');
        }

        return view('app');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['password'] = Hash::make($request->password);

            $user = User::create($data);

            DB::commit();

            return $this->success($user->load('role'), 'User created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        if (!auth()->user()->can('view_users')) {
            abort(403, 'Unauthorized action.');
        }

        $user->load('role');

        return $this->success($user, 'User retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if (!auth()->user()->can('edit_user')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'status' => 'in:active,inactive',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return $this->success($user, 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if (!auth()->user()->can('delete_user')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return $this->error('Cannot delete your own account', 400);
        }

        try {
            DB::beginTransaction();

            $user->delete();

            DB::commit();

            return $this->success(null, 'User deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Get user statistics
     */
    public function stats(Request $request)
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('status', 'active')->count(),
                'inactive_users' => User::where('status', 'inactive')->count(),
                'by_role' => User::with('role:id,name')
                    ->selectRaw('role_id, COUNT(*) as count')
                    ->groupBy('role_id')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'role_name' => $item->role->name ?? 'No Role',
                            'count' => $item->count,
                        ];
                    }),
                'recent_users' => User::with('role:id,name')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get(['id', 'name', 'email', 'role_id', 'created_at']),
            ];

            return $this->success($stats, 'User statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        if (!auth()->user()->can('edit_user')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent deactivating own account
        if ($user->id === auth()->id()) {
            return $this->error('Cannot deactivate your own account', 400);
        }

        try {
            DB::beginTransaction();

            $user->status = $user->status === 'active' ? 'inactive' : 'active';
            $user->save();

            DB::commit();

            $status = $user->status === 'active' ? 'activated' : 'deactivated';
            return $this->success($user, "User {$status} successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }
}
