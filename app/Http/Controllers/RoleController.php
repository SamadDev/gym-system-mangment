<?php

namespace App\Http\Controllers;

use App\Constants\DateTimeConstants;
use App\Models\Role;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    use HttpResponses;

    /**
     * Get DataTables data for roles listing
     */
    public function data(Request $request)
    {
        $roles = Role::select(['id', 'name', 'description', 'created_at'])
            ->withCount('users');

        return DataTables::eloquent($roles)
            ->editColumn('created_at', function ($data) {
                return $data->created_at?->timezone(DateTimeConstants::TIMEZONE)
                    ->format(DateTimeConstants::DISPLAY_DATETIME_FORMAT);
            })
            ->only(['id', 'name', 'description', 'users_count', 'created_at'])
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('view_roles')) {
            abort(403, 'Unauthorized action.');
        }

        return view('app');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        try {
            DB::beginTransaction();

            $role = Role::create($request->validated());

            DB::commit();

            return $this->success($role, 'Role created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        if (!auth()->user()->can('view_roles')) {
            abort(403, 'Unauthorized action.');
        }

        $role->load(['users:id,name,email,role_id']);

        return $this->success($role, 'Role retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        try {
            DB::beginTransaction();

            $role->update($request->validated());

            DB::commit();

            return $this->success($role, 'Role updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if (!auth()->user()->can('delete_role')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent deleting role if it has users
        if ($role->users()->exists()) {
            return $this->error('Cannot delete role with assigned users', 400);
        }

        try {
            DB::beginTransaction();

            $role->delete();

            DB::commit();

            return $this->success(null, 'Role deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Get all available permissions
     */
    public function permissions()
    {
        if (!auth()->user()->can('view_roles')) {
            abort(403, 'Unauthorized action.');
        }

        // Define all available permissions grouped by module
        $permissions = [
            'Members' => [
                'view_members',
                'add_member',
                'edit_member',
                'delete_member',
            ],
            'Memberships' => [
                'view_memberships',
                'add_membership',
                'edit_membership',
                'cancel_membership',
                'renew_membership',
            ],
            'Attendance' => [
                'view_attendance',
                'check_in',
                'check_out',
            ],
            'Payments' => [
                'view_payments',
                'add_payment',
            ],
            'Invoices' => [
                'view_invoices',
                'print_invoice',
            ],
            'Inventory' => [
                'view_inventory',
                'add_inventory',
                'edit_inventory',
                'delete_inventory',
            ],
            'Expenses' => [
                'view_expenses',
                'add_expense',
                'edit_expense',
                'delete_expense',
            ],
            'Trainers' => [
                'view_trainers',
                'add_trainer',
                'edit_trainer',
                'delete_trainer',
            ],
            'Equipment' => [
                'view_equipment',
                'add_equipment',
                'edit_equipment',
                'delete_equipment',
            ],
            'Reports' => [
                'view_reports',
                'export_reports',
            ],
            'Roles & Users' => [
                'view_roles',
                'add_role',
                'edit_role',
                'delete_role',
                'view_users',
                'add_user',
                'edit_user',
                'delete_user',
            ],
        ];

        return $this->success($permissions, 'Permissions retrieved successfully');
    }
}
