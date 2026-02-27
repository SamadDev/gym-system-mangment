<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use App\Models\Trainer;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of classes
     */
    public function index()
    {
        return view('app');
    }

    /**
     * Get classes data for DataTable
     */
    public function data(Request $request)
    {
        try {
            $query = DB::table('classes')
                ->leftJoin('trainers', 'classes.trainer_id', '=', 'trainers.id')
                ->select('classes.*', 'trainers.first_name as trainer_first_name', 'trainers.last_name as trainer_last_name');

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('classes.name', 'like', "%{$search}%")
                      ->orWhere('classes.description', 'like', "%{$search}%");
                });
            }

            $classes = $query->get();

            return $this->success($classes);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created class
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'trainer_id' => 'nullable|exists:trainers,id',
                'description' => 'nullable|string',
                'capacity' => 'nullable|integer|min:1',
                'duration_minutes' => 'required|integer|min:15',
                'difficulty_level' => 'nullable|in:beginner,intermediate,advanced',
                'gender_type' => 'required|in:male,female,mixed',
                'status' => 'nullable|in:active,inactive',
            ]);

            $classId = DB::table('classes')->insertGetId(array_merge($validated, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            $class = DB::table('classes')->where('id', $classId)->first();

            return $this->success($class, 'Class created successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    /**validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'trainer_id' => 'sometimes|nullable|exists:trainers,id',
                'description' => 'nullable|string',
                'capacity' => 'nullable|integer|min:1',
                'duration_minutes' => 'sometimes|required|integer|min:15',
                'difficulty_level' => 'nullable|in:beginner,intermediate,advanced',
                'gender_type' => 'sometimes|required|in:male,female,mixed',
                'status' => 'nullable|in:active,inactive',
            ]);

            DB::table('classes')
                ->where('id', $id)
                ->update(array_merge($validated, ['updated_at' => now()]));

            $class = DB::table('classes')->where('id', $id)->first(etimes|required|integer|min:15',
                'max_capacity' => 'sometimes|required|integer|min:1',
                'description' => 'nullable|string',
            ]);

            $class->update($validated);
            $class->load('trainer');

            return $this->success($class, 'Class updated successfully');
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified class
     */
    public function destroy($id)
    {
        try {
            DB::table('classes')->where('id', $id)->delete();

            return $this->success(null, 'Class deleted successfully');
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
}
