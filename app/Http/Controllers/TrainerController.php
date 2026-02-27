<?php

namespace App\Http\Controllers;

use App\Constants\DateTimeConstants;
use App\Http\Requests\StoreTrainerRequest;
use App\Http\Requests\UpdateTrainerRequest;
use App\Models\Trainer;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TrainerController extends Controller
{
    use HttpResponses;

    /**
     * Get DataTables data for trainers listing
     */
    public function data(Request $request)
    {
        $trainers = Trainer::tableSelect()->tableRelation()->tableFilter();

        return DataTables::eloquent($trainers)
            ->editColumn('full_name', function ($data) {
                return $data->full_name;
            })
            ->editColumn('certifications', function ($data) {
                return is_array($data->certifications) ? implode(', ', $data->certifications) : $data->certifications;
            })
            ->editColumn('salary', function ($data) {
                return number_format($data->salary, 2) . ' IQD';
            })
            ->editColumn('commission_rate', function ($data) {
                return $data->commission_rate ? number_format($data->commission_rate, 2) . '%' : 'N/A';
            })
            ->editColumn('years_of_service', function ($data) {
                return $data->years_of_service . ' years';
            })
            ->editColumn('status', function ($data) {
                return ucfirst(str_replace('_', ' ', $data->status));
            })
            ->editColumn('photo', function ($data) {
                return $data->photo ? asset('storage/' . $data->photo) : null;
            })
            ->editColumn('created_at', function ($data) {
                return $data->created_at?->timezone(DateTimeConstants::TIMEZONE)
                    ->format(DateTimeConstants::DISPLAY_DATETIME_FORMAT);
            })
            ->only(['id', 'full_name', 'phone', 'email', 'certifications', 'salary', 'commission_rate', 'hire_date', 'years_of_service', 'status', 'photo', 'created_at'])
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('view_trainers')) {
            abort(403, 'Unauthorized action.');
        }

        return view('app');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTrainerRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('trainers/photos', 'public');
            }

            $trainer = Trainer::create($data);

            DB::commit();

            return $this->success($trainer, 'Trainer created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Trainer $trainer)
    {
        if (!auth()->user()->can('view_trainers')) {
            abort(403, 'Unauthorized action.');
        }

        $trainer->load(['classes']);

        return $this->success($trainer, 'Trainer retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTrainerRequest $request, Trainer $trainer)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo
                if ($trainer->photo) {
                    Storage::disk('public')->delete($trainer->photo);
                }
                $data['photo'] = $request->file('photo')->store('trainers/photos', 'public');
            }

            $trainer->update($data);

            DB::commit();

            return $this->success($trainer, 'Trainer updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trainer $trainer)
    {
        if (!auth()->user()->can('delete_trainer')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            // Delete photo if exists
            if ($trainer->photo) {
                Storage::disk('public')->delete($trainer->photo);
            }

            $trainer->delete();

            DB::commit();

            return $this->success(null, 'Trainer deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    /**
     * Get trainer statistics
     */
    public function stats(Request $request)
    {
        try {
            $stats = [
                'total_trainers' => Trainer::count(),
                'active_trainers' => Trainer::where('status', 'active')->count(),
                'on_leave_trainers' => Trainer::where('status', 'on_leave')->count(),
                'terminated_trainers' => Trainer::where('status', 'terminated')->count(),
                'total_salary_expense' => Trainer::where('status', 'active')->sum('salary'),
                'total_commission_pool' => Trainer::where('status', 'active')->sum('commission_rate'),
                'by_status' => Trainer::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->get(),
                'recent_hires' => Trainer::orderBy('hire_date', 'desc')
                    ->limit(5)
                    ->get(['id', 'first_name', 'last_name', 'hire_date', 'status']),
            ];

            return $this->success($stats, 'Trainer statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->exception($e);
        }
    }
}
