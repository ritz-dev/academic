<?php

namespace App\Http\Controllers\APIs;

use Exception;
use App\Models\Subject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string',
                'limit' => 'nullable|integer|min:1',
                'skip' => 'nullable|integer|min:0',
            ]);
        
            $query = Subject::when(!empty($validated['name']), fn($q) => $q->where('name', 'like', '%' . $validated['name'] . '%'));
        
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

            $subject = Subject::create($validated);

            return response()->json([
                'message' => 'Academic subject created successfully.',
                'data' => $subject
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the academic subject.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:subjects,slug',
            ]);

            $subject = Subject::withTrashed()->where('slug', $validated['slug'])->firstOrFail();

            if($subject->trashed()) {
                return response()->json([
                    'message' => 'Academic subject not found.',
                ], 404);
            }

            return response()->json($subject);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'message' => 'Academic subject not found.',
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

            $subject = Subject::where('slug', $request->slug)->firstOrFail();

            $validated = $request->validate([
                'slug' => ['required', 'string', 'exists:subjects,slug'],
                'name' => ['required', 'string', 'max:255'],
            ]);

            $subject->update($validated);

            return response()->json([
                'message' => 'Subject created successfully.',
                'data' => $academicSection
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the subject.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function handleAction(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:subjects,slug',
                'action' => 'required|string|in:delete,restore',
            ]);

            $slug = $validated['slug'];
            $action = $validated['action'];

            $subject = Subject::withTrashed()->where('slug', $slug)->firstOrFail();

            switch ($action) {
                case 'delete':
                    $subject->delete();
                    return response()->json(['message' => 'Academic subject soft-deleted']);

                case 'restore':
                    if ($subject->trashed()) {
                        $subject->restore();
                        return response()->json(['message' => 'Academic subject restored']);
                    }
                    
                    return response()->json(['message' => 'Academic subject is not deleted'], 400);

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
                'message' => 'Academic subject not found.',
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
