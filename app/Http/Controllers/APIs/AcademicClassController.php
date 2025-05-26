<?php

namespace App\Http\Controllers\APIs;

use Exception;
use Illuminate\Support\Str;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Models\AcademicClass;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\AcademicClassResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AcademicClassController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(AcademicClass::get());

        // try {
        //     $limit = (int) $request->limit;
        //     $search = $request->search;

        //     $query = AcademicClass::orderBy('id', 'desc');

        //     if ($search) {
        //         $query->where('name', 'LIKE', $search . '%');
        //     }

        //     $data = $limit ? $query->paginate($limit) : $query->get();

        //     $data = AcademicClassResource::collection($data);

        //     $total = AcademicClass::count();

        //     return response()->json([
        //         "status" => "OK! The request was successful",
        //         "total" => $total,
        //         "data" => $data
        //     ], 200);
        // } catch (Exception $e) {
        //     return response()->json([
        //         'status' => 'Bad Request!. The request is invalid.',
        //         'message' => $e->getMessage()
        //     ],400);
        // }
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
