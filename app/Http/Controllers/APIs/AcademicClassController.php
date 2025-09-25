<?php

namespace App\Http\Controllers\APIs;

use Exception;
use Illuminate\Http\Request;
use App\Models\AcademicClass;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AcademicClassController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string',
                'limit'=>'nullable|integer|min:1',
                'skip' => 'nullable|integer|min:0',
                'notIn'=> 'nullable|array',
            ]);
        
            $query = AcademicClass::query()
                ->when(!empty($validated['name']), fn($q) => $q->where('name', 'like', '%' . $validated['name'] . '%'))
                ->when(!empty($validated['notIn']), fn($q)=> $q->whereNotIn('slug', $notIn));
        
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
                'name' => 'required|string|max:255',
            ]);

            $academicClass = AcademicClass::create($validated);

            return response()->json([
                'message' => 'Academic class created successfully.',
                'data' => $academicClass
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the academic class.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:academic_classes,slug',
            ]);

            $academicClass = AcademicClass::withTrashed()->where('slug', $validated['slug'])->firstOrFail();

            if($academicClass->trashed()) {
                return response()->json([
                    'message' => 'Academic class not found.',
                ], 404);
            }

            return response()->json($academicClass);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'message' => 'Academic class not found.',
                'error' => $e->getMessage()
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {

            $academicClass = AcademicClass::where('slug', $request->slug)->firstOrFail();

            $validated = $request->validate([
                'slug' => ['required', 'string', 'exists:academic_classes,slug'],
                'name' => ['required', 'string', 'max:255'],
            ]);

            $academicClass->update($validated);

            return response()->json([
                'message' => 'Academic class created successfully.',
                'data' => $academicClass
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the academic class.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function handleAction(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:academic_classes,slug',
                'action' => 'required|string|in:delete,restore',
            ]);

            $slug = $validated['slug'];
            $action = $validated['action'];

            $academicClass = AcademicClass::withTrashed()->where('slug', $slug)->firstOrFail();

            switch ($action) {
                case 'delete':
                    $academicClass->delete();
                    return response()->json(['message' => 'Academic class soft-deleted']);

                case 'restore':
                    if ($academicClass->trashed()) {
                        $academicClass->restore();
                        return response()->json(['message' => 'Academic class restored']);
                    }
                    return response()->json(['message' => 'Academic class is not deleted'], 400);

                default:
                    return response()->json(['message' => 'Invalid action'], 400);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Academic class not found.',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
