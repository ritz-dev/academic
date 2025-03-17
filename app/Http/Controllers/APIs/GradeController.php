<?php

namespace App\Http\Controllers\APIs;

use Exception;
use Carbon\Carbon;
use App\Models\Grade;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\BlockChainService;
use App\Http\Controllers\Controller;
use App\Http\Resources\GradeResource;
use Illuminate\Validation\ValidationException;

class GradeController extends Controller
{
    public function list(Request $request)
    {
        try {
            $limit = (int) $request->limit;
            $search = $request->search;

            $query = Grade::orderBy('id', 'desc');

            if ($search) {
                $query->where('name', 'LIKE', $search . '%');
            }

            $data = $limit ? $query->paginate($limit) : $query->get();

            $data = GradeResource::collection($data);

            $total = Grade::count();

            return response()->json([
                "status" => "OK! The request was successful",
                "total" => $total,
                "data" => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'Bad Request!. The request is invalid.',
                'message' => $e->getMessage()
            ],400);
        }
    }

    public function create(Request $request)
    {
        try {

            $request->validate([
                'name' => 'required|unique:grades,name'
            ]);

            $grade = new Grade;
            $grade->slug = Str::uuid();
            $grade->name = $request->name;
            $grade->save();

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
                'slug' => 'required|exists:grades,slug'
            ]);

            $data = Grade::where('slug',$request->slug)->firstOrFail();
            $grade = new GradeResource($data);
            return response()->json($grade,200);

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

            $grade = Grade::where('slug',$request->slug)->firstOrFail();

             $request->validate([
                'name' => 'required|unique:grades,name,'.$grade->id,
            ]);

            $grade->name = $request->name;
            $grade->save();

            $grade = new GradeResource($grade);
            return response()->json($grade, 200);
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
            $grade = Grade::where('slug',$request->slug)->firstOrFail();
            $grade->delete();

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
}
