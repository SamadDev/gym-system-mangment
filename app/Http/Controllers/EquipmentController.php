<?php

namespace App\Http\Controllers;

use App\Constants\DateTimeConstants;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Models\Equipment;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EquipmentController extends Controller
{
    use HttpResponses;

    /**
     * Get DataTables data for equipment listing
     */
    public function data(Request $request)
    {
        $equipment = Equipment::tableSelect()->tableRelation()->tableFilter();

        return DataTables::eloquent($equipment)
            ->editColumn('equipment_code', function ($data) {
                return $data->equipment_code;
            })
            ->editColumn('purchase_price', function ($data) {
                return $data->purchase_price ? number_format($data->purchase_price, 2) . ' IQD' : 'N/A';
            })
            ->editColumn('age_in_years', function ($data) {
                return $data->age_in_years . ' years';
            })
            ->editColumn('condition', function ($data) {
                return ucfirst($data->condition);
            })
            ->editColumn('status', function ($data) {
                return ucfirst(str_replace('_', ' ', $data->status));
            })
            ->editColumn('next_maintenance_date', function ($data) {
                if (!$data->next_maintenance_date) return 'N/A';
                $isPastDue = $data->next_maintenance_date < now();
                $class = $isPastDue ? 'text-red-600' : 'text-gray-800';
                return '<span class="' . $class . '">' . $data->next_maintenance_date->format('Y-m-d') . '</span>';
            })
            ->editColumn('created_at', function ($data) {
                return $data->created_at?->timezone(DateTimeConstants::TIMEZONE)
                    ->format(DateTimeConstants::DISPLAY_DATETIME_FORMAT);
            })
            ->only(['id', 'name', 'equipment_code', 'category', 'purchase_price', 'age_in_years', 'condition', 'last_maintenance_date', 'next_maintenance_date', 'status', 'notes', 'created_at'])
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('view_equipment')) {
            abort(403, 'Unauthorized action.');
        }

        return view('app');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEquipmentRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('equipment/images', 'public');
            }

            $equipment = Equipment::create($data);

            DB::commit();

            return $this->success($equipment, 'Equipment created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEquipmentRequest $request, Equipment $equipment)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($equipment->image) {
                    Storage::disk('public')->delete($equipment->image);
                }
                $data['image'] = $request->file('image')->store('equipment/images', 'public');
            }

            $equipment->update($data);

            DB::commit();

            return $this->success($equipment, 'Equipment updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Equipment $equipment)
    {
        if (!auth()->user()->can('delete_equipment')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            // Delete image if exists
            if ($equipment->image) {
                Storage::disk('public')->delete($equipment->image);
            }

            $equipment->delete();

            DB::commit();

            return $this->success(null, 'Equipment deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Get equipment statistics
     */
    public function stats(Request $request)
    {
        try {
            $stats = [
                'total_equipment' => Equipment::count(),
                'active_equipment' => Equipment::where('status', 'active')->count(),
                'under_maintenance_equipment' => Equipment::where('status', 'under_maintenance')->count(),
                'retired_equipment' => Equipment::where('status', 'retired')->count(),
                'needs_maintenance' => Equipment::where('next_maintenance_date', '<=', now()->addDays(7))->count(),
                'total_value' => Equipment::sum('purchase_price'),
                'by_category' => Equipment::selectRaw('category, COUNT(*) as count')
                    ->groupBy('category')
                    ->get(),
                'by_condition' => Equipment::selectRaw('condition, COUNT(*) as count')
                    ->groupBy('condition')
                    ->get(),
                'by_status' => Equipment::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->get(),
                'maintenance_due_soon' => Equipment::where('next_maintenance_date', '>=', now())
                    ->where('next_maintenance_date', '<=', now()->addDays(30))
                    ->get(['id', 'name', 'next_maintenance_date']),
            ];

            return $this->success($stats, 'Equipment statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }
}
