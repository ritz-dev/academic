<?php

namespace App\Http\Controllers\APIs;

use Exception;
use App\Models\Section;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string',
                'limit' => 'nullable|integer|min:1',
                'skip' => 'nullable|integer|min:0',
                'notIn'=> 'nullable|array',
            ]);
        
            $query = Section::query()
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

            $seciton = Section::create($validated);

            return response()->json([
                'message' => 'Academic sections created successfully.',
                'data' => $seciton
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the academic section.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:sections,slug',
            ]);

            $section = Section::withTrashed()->where('slug', $validated['slug'])->firstOrFail();

            if($section->trashed()) {
                return response()->json([
                    'message' => 'Academic section not found.',
                ], 404);
            }

            return response()->json($section);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'message' => 'Academic section not found.',
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

            $academicSection = Section::where('slug', $request->slug)->firstOrFail();

            $validated = $request->validate([
                'slug' => ['required', 'string', 'exists:sections,slug'],
                'name' => ['required', 'string', 'max:255'],
            ]);

            $academicSection->update($validated);

            return response()->json([
                'message' => 'Academic section created successfully.',
                'data' => $academicSection
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the academic section.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function handleAction(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:sections,slug',
                'action' => 'required|string|in:delete,restore',
            ]);

            $slug = $validated['slug'];
            $action = $validated['action'];

            $section = Section::withTrashed()->where('slug', $slug)->firstOrFail();

            switch ($action) {
                case 'delete':
                    $section->delete();
                    return response()->json(['message' => 'Academic section soft-deleted']);

                case 'restore':
                    if ($section->trashed()) {
                        $section->restore();
                        return response()->json(['message' => 'Academic section restored']);
                    }
                    return response()->json(['message' => 'Academic section is not deleted'], 400);

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
                'message' => 'Academic section not found.',
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
