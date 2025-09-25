<?php

namespace App\Http\Controllers\APIs;

use Exception;
use App\Models\Exam;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\ExamResource;
use Illuminate\Validation\ValidationException;

class ExamController extends Controller
{
    public function list(Request $request)
    {
        try {
            $limit = (int) $request->limit;
            $search = $request->search;

            $query = Exam::orderBy('id', 'desc');

            if ($search) {
                $query->where('name', 'LIKE', $search . '%');
            }

            $data = $limit ? $query->paginate($limit) : $query->get();

            $data = ExamResource::collection($data);

            $total = Exam::count();

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
                'name' => 'required'
            ]);

            $exam = new Exam;
            $exam->slug = Str::uuid();
            $exam->name = $request->name;
            $exam->save();

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
                'slug' => 'required|exists:exams,slug'
            ]);

            $data = Exam::where('slug',$request->slug)->firstOrFail();
            logger($data);
            $exam = new ExamResource($data);
            return response()->json($exam,200);

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
            $exam = Exam::where('slug',$request->slug)->firstOrFail();

            $request->validate([
                'name' => [
                    'required',
                    Rule::unique('exams')->ignore($exam->id),
                ],
            ]);

            $exam->name = $request->name;
            $exam->save();

            $exam = new ExamResource($exam);
            return response()->json($exam, 200);
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
            $exam = Exam::where('slug',$request->slug)->firstOrFail();
            $exam->delete();

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
