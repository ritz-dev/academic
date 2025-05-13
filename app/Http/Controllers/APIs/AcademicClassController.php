<?php

namespace App\Http\Controllers\APIs;

use Exception;
use App\Models\Section;
use Illuminate\Support\Str;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Models\AcademicClass;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
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

    public function create(Request $request)
    {
        try{
            $request->validate([
                'name' => 'required|string|unique:academic_classes,name',
                'limit'=> 'required|numeric',
                'academicYear' => 'required|string',
            ]);

            $year = AcademicYear::where('slug',$request->academicYear)->firstOrFail();
            $academic_class = new AcademicClass;
            $academic_class->slug = Str::uuid();
            $academic_class->name = $request->name;
            $academic_class->limit = $request->limit;
            $academic_class->academic_year_id = $year->id;
            $academic_class->save();

            return response()->json([
                "status" => "OK! The request was successful",
            ],200);

        }catch (ValidationException $e) {
            return response()->json([
                'status' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while adding.',
                'message' => $e->getMessage()
            ], 500);
        }

    }
    public function detail(Request $request)
    {
        try {
            $request->validate([
                'slug' => 'required|exists:academic_classes,slug'
            ]);

            $data = AcademicClass::where('slug',$request->slug)->firstOrFail();
            $academic_class = new AcademicClassResource($data);
            return response()->json($academic_class,200);

        }catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while showing.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try{
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    Rule::unique('academic_classes')->ignore($request->slug,'slug'),
                ],
                'limit'=> 'required|numeric',
                'academicYearId' => 'required',
            ]);

            $class = AcademicClass::where('slug',$request->slug)->firstOrFail();
            $class->name = $request->name;
            $class->limit = $request->limit;
            $class->academic_year_id = $request->academicYearId;
            $class->save();

            return response()->json($class, 200);
        }catch (ValidationException $e) {
            return response()->json([
                'status' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while editing.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $academic_class = AcademicClass::where('slug',$request->slug)->firstOrFail();
            $academic_class->delete();

            return response()->json([
                "status" => "OK! Deleting Successfully."
            ],200);
        }catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while deleting.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function byYear(Request $request)
    {
        try {

            $request->validate([
                'yearId' => 'required',
            ]);

            $year = AcademicYear::where('slug',$request->yearId)->firstOrFail();

            $data = AcademicClass::where('academic_year_id',$year->id)
                    ->select(['slug','name','limit'])
                    ->get();

            return response()->json($data);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ],500);
        }
    }
}
