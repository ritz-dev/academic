<?php

namespace App\Http\Controllers\APIs;

use Exception;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class AcademicYearController extends Controller
{

    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['nullable', 'string'],
                'start_date' => ['nullable', 'date'],
                'end_date' => ['nullable', 'date'],
                'status' => ['nullable', 'string'],
                'limit' => ['nullable', 'integer', 'min:1'],
                'skip' => ['nullable', 'integer', 'min:0'],
            ]);

            $query = AcademicYear::query()
                ->when(!empty($validated['name']), fn($q) =>
                    $q->where('year', 'like', '%' . $validated['name'] . '%'))
                ->when(!empty($validated['status']), fn($q) =>
                    $q->where('status', $validated['status']))
                ->when(!empty($validated['start_date']) && !empty($validated['end_date']), function ($q) use ($validated) {
                    $startInt = (int) Carbon::parse($validated['start_date'])->format('Ymd');
                    $endInt = (int) Carbon::parse($validated['end_date'])->format('Ymd');
                    $q->whereBetween('start_date', [$startInt, $endInt]);
                })
                ->when(!empty($validated['start_date']) && empty($validated['end_date']), function ($q) use ($validated) {
                    $startInt = (int) Carbon::parse($validated['start_date'])->format('Ymd');
                    $q->where('start_date', '>=', $startInt);
                })
                ->when(!empty($validated['end_date']) && empty($validated['start_date']), function ($q) use ($validated) {
                    $endInt = (int) Carbon::parse($validated['end_date'])->format('Ymd');
                    $q->where('start_date', '<=', $endInt);
                })
                ->orderByDesc('start_date');

            $total = (clone $query)->count();

            if (!empty($validated['skip'])) {
                $query->skip($validated['skip']);
            }

            if (!empty($validated['limit'])) {
                $query->take($validated['limit']);
            }

            $results = $query->get()->map(function ($item) {
                return [...$item->toArray()];
            });

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

            $validated['start_date'] = (int) Carbon::parse($validated['start_date'])->format('Ymd');
            $validated['end_date'] = (int) Carbon::parse($validated['end_date'])->format('Ymd');
            
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

    public function update(Request $request)
    {
        try {

            $academicYear = AcademicYear::where('slug', $request->slug)->firstOrFail();

            $validated = $request->validate([
                'slug' => ['required', 'string', 'exists:academic_years,slug'],
                'year' => ['required', 'string', Rule::unique('academic_years', 'year')->ignore($academicYear->id)],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
                'status' => ['required', 'in:Upcoming,In Progress,Completed'],
            ]);

            $validated['start_date'] = (int) Carbon::parse($validated['start_date'])->format('Ymd');
            $validated['end_date'] = (int) Carbon::parse($validated['end_date'])->format('Ymd');

            $academicYear->update($validated);

            return response()->json([
                'message' => 'Academic year updated successfully.',
                'data' => $academicYear,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the academic year.',
                'error' => $e->getMessage(),
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
