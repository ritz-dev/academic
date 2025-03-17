<?php

namespace App\Http\Controllers\APIs;

use Exception;
use App\Models\Section;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\AcademicClass;
use App\Models\StudentSection;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\SectionResource;
use Illuminate\Validation\ValidationException;


class SectionController extends Controller
{
    public function list(Request $request)
    {
        try {
            $limit = (int) $request->limit;
            $search = $request->search;

            $query = Section::orderBy('id', 'desc');

            if ($search) {
                $query->where('name', 'LIKE', $search . '%');
            }

            $data = $limit ? $query->paginate($limit) : $query->get();

            $data = SectionResource::collection($data);

            $total = Section::count();

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
                'name' => 'required',
                'classId' => "required|exists:academic_classes,slug"
            ]);

            $academic_class = AcademicClass::where('slug',$request->classId)->firstOrFail();

            $section = new Section;
            $section->slug = Str::uuid();
            $section->name = $request->name;
            $section->limit = $request->limit;
            $section->teacher_id = $request->teacherId;
            $section->academic_class_id = $academic_class->id;
            $section->save();

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
                'slug' => 'required|exists:sections,slug'
            ]);

            $data = Section::where('slug',$request->slug)->firstOrFail();
            $class = AcademicClass::where('id',$data->academic_class_id)->firstOrFail();
            $data->academic_class_id = $class->slug;
            $section = new SectionResource($data);
            return response()->json($section,200);

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
                'slug' => 'required|exists:sections,slug',
                'name' => 'required',
                'classId' => 'required',
            ]);

            $class = AcademicClass::where('slug',$request->classId)->firstOrFail();

            $section = Section::where('slug',$request->slug)->firstOrFail();
            $section->name = $request->name;
            $section->teacher_id = $request->teacherId;
            $section->limit = $request->limit;
            $section->academic_class_id = $class->id;
            $section->save();

            return response()->json([
                "status" => "OK! The request was updated",
            ],200);
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
            $section = Section::where('slug',$request->slug)->firstOrFail();
            $section->delete();

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

    public function byClass(Request $request)
    {
        try {
            $request->validate([
                'classId' => 'required',
            ]);

            $class = AcademicClass::where('slug',$request->classId)->firstOrFail();

            $data = Section::where('academic_class_id',$class->id)
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

    public function StudentsInSection(Request $request)
    {
        try {
            $request->validate([
                'slug' => 'required|exists:sections,slug',
            ]);

            $section = Section::where('slug', $request->slug)->firstOrFail();

            $userManagementServiceUrl = config('services.user_management.url') . '/students/by-section';

            // Fetch teacher info based on the section ID
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => $request->header('Authorization'),
            ])->post($userManagementServiceUrl, [
                'sectionId' => $section->id
            ]);

            // Check if the response is successful
            if ($response->failed()) {
                return response()->json(['error' => 'Unable to fetch teacher info'], 400);
            }

            // Parse the response data
            return $response->json();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ],500);
        }
    }
}
