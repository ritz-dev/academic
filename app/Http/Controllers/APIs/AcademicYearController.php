<?php

namespace App\Http\Controllers\APIs;

use Exception;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class AcademicYearController extends Controller
{

    public function index(Request $request)
    {
        return response()->json(AcademicYear::get());
        try {
            $validated = $request->validate([
                'name' => 'nullable|string',
                'limit' => 'nullable|integer|min:1',
                'skip' => 'nullable|integer|min:0',
            ]);
        
            $query = AcademicYear::when(!empty($validated['name']), fn($q) => $q->where('name', 'like', '%' . $validated['name'] . '%'));
        
            $total = (clone $query)->count();
        
            // Apply skip and limit
            if (!empty($validated['skip'])) {
                $query->skip($validated['skip']);
            }
            if (!empty($validated['limit'])) {
                $query->take($validated['limit']);
            }
        
            $results = $query->get();
        
            return response()->json([
                'status' => 'OK! The request was successful',
                'total' => $total,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Bad Request! The request is invalid.',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'year' => 'required|unique:academic_years,year',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'status' => 'in:Upcoming,In Progress,Completed',
            ]);

            $academicYear = AcademicYear::create($validated);

            return response()->json([
                'message' => 'Academic year created successfully.',
                'data' => $academicYear
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the academic year.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:academic_years,slug',
            ]);

            $academicYear = AcademicYear::where('slug', $validated['slug'])->firstOrFail();

            if($academicYear->trashed()) {
                return response()->json([
                    'message' => 'Academic year not found.',
                ], 404);
            }

            return response()->json($academicYear);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Academic year not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving the academic year.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handleAction(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:academic_years,slug',
                'action' => 'required|string|in:upcoming,in_progress,completed,restore,delete',
            ]);

            $slug = $validated['slug'];
            $action = $validated['action'];

            // Support soft-deleted models as well
            $academicYear = AcademicYear::withTrashed()->where('slug', $slug)->firstOrFail();

            switch ($action) {
                case 'upcoming':
                    $academicYear->status = 'Upcoming';
                    $academicYear->save();
                    return response()->json(['message' => 'Academic year status set to Upcoming']);

                case 'in_progress':
                    $academicYear->status = 'In Progress';
                    $academicYear->save();
                    return response()->json(['message' => 'Academic year status set to In Progress']);

                case 'completed':
                    $academicYear->status = 'Completed';
                    $academicYear->save();
                    return response()->json(['message' => 'Academic year status set to Completed']);

                case 'delete':
                    $academicYear->delete();
                    return response()->json(['message' => 'Academic year soft-deleted']);

                case 'restore':
                    if ($academicYear->trashed()) {
                        $academicYear->restore();
                        return response()->json(['message' => 'Academic year restored']);
                    }
                    return response()->json(['message' => 'Academic year is not deleted'], 400);

                default:
                    return response()->json(['message' => 'Invalid action'], 400);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Academic year not found.',
                'error' => $e->getMessage(),
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while handling the action.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
