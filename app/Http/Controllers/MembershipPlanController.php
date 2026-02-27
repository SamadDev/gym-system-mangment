<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;

class MembershipPlanController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of membership plans
     */
    public function index()
    {
        return view('app');
    }

    /**
     * Get membership plans data for DataTable
     */
    public function data(Request $request)
    {
        $plans = MembershipPlan::query();

        return DataTables::eloquent($plans)
            ->addColumn('duration_text', function ($plan) {
                return $plan->duration . ' ' . $plan->duration_type;
            })
            ->addColumn('price_formatted', function ($plan) {
                return number_format($plan->price, 2);
            })
            ->only(['id', 'name', 'duration', 'duration_type', 'duration_text', 'price', 'price_formatted', 'description', 'status'])
            ->make(true);
    }

    /**
     * Store a newly created membership plan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'duration_type' => 'required|in:day,week,month,year',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            $plan = MembershipPlan::create($validated);
            return $this->success($plan, 'Membership plan created successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified membership plan
     */
    public function show($id)
    {
        try {
            $plan = MembershipPlan::findOrFail($id);
            return $this->success($plan);
        } catch (\Exception $e) {
            return $this->error(null, 'Membership plan not found', 404);
        }
    }

    /**
     * Update the specified membership plan
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'duration_type' => 'required|in:day,week,month,year',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            $plan = MembershipPlan::findOrFail($id);
            $plan->update($validated);
            return $this->success($plan, 'Membership plan updated successfully');
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified membership plan
     */
    public function destroy($id)
    {
        try {
            $plan = MembershipPlan::findOrFail($id);
            $plan->delete();
            return $this->success(null, 'Membership plan deleted successfully');
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Get active membership plans for dropdown
     */
    public function active()
    {
        $plans = MembershipPlan::where('status', 'active')->get();
        return $this->success($plans);
    }
}
