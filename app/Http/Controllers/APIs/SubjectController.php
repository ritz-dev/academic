<?php

namespace App\Http\Controllers\APIs;

use Exception;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SectionSubject;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubjectResource;
use Illuminate\Validation\ValidationException;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Subject::get());
        // try {
        //     $limit = (int) $request->limit;
        //     $search = $request->search;

        //     $query = Subject::orderBy('id', 'desc');

        //     if ($search) {
        //         $query->where('name', 'LIKE', $search . '%');
        //     }

        //     $data = $limit ? $query->paginate($limit) : $query->get();

        //     $data = SubjectResource::collection($data);

        //     $total = Subject::count();

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
        try {

            $request->validate([
                'name' => 'required',
                'code' => 'required|unique:subjects,code',
                'sectionId' => "required|exists:sections,slug"
            ]);

            $section = Section::where('slug',$request->sectionId)->firstOrFail();

            $subject = new Subject;
            $subject->slug = Str::uuid();
            $subject->name = $request->name;
            $subject->code = $request->code;
            $subject->description = $request->description;
            $subject->academic_class_id = $section->academic_class_id;
            $subject->save();

            $section_subject = new SectionSubject;
            $section_subject->section_id = $section->id;
            $section_subject->subject_id = $subject->id;
            $section_subject->save();

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
                'slug' => 'required|exists:subjects,slug'
            ]);

            $data = Subject::where('slug',$request->slug)->firstOrFail();
            $subject = new SubjectResource($data);
            return response()->json($subject,200);

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

            $subject = Subject::where('slug',$request->slug)->firstOrFail();
             $request->validate([
                'name' => 'required',
                'code' => 'required|unique:subjects,code,'.$subject->code,
                'academicClassId' => 'required',
            ]);

            $subject->name = $request->name;
            $subject->code = $request->code;
            $subject->description = $request->description;
            $subject->academic_class_id = $request->academicClassId;
            $subject->save();

            $subject = new SubjectResource($subject);
            return response()->json($subject, 200);
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
            $subject_slug = $request->slug;
            $section_slug = $request->options;

            $section_id = Section::where('slug',$section_slug)->pluck('id')->firstOrFail();
            $subject = Subject::where('slug',$subject_slug)->firstOrFail();
            SectionSubject::where('section_id',$section_id)
                                            ->where('subject_id',$subject->id)
                                            ->delete();
            $subject->delete();

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

    public function bySection(Request $request)
    {
        $request->validate([
            'sectionId' => 'required',
        ]);

        try {

            $section = Section::where('slug',$request->sectionId)->firstOrFail();

            $data = SectionSubject::where('section_id', $section->id)
                            ->join('subjects', 'subjects.id', '=', 'sections_subjects.subject_id')
                            ->select('subjects.slug', 'subjects.name', 'subjects.description', 'subjects.code')
                            ->get();

            return response()->json($data);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function notInSection(Request $request)
    {
        try {
            $request->validate([
                'sectionId' => 'required',
            ]);

            $section = Section::where('slug',$request->sectionId)->firstOrFail();

            // Get the academic class id for the section
            $academic_class_id = Section::where('id', $section->id)
                            ->value('academic_class_id');

            if (!$academic_class_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Section not found.'
                ], 400);
            }

            // Retrieve assigned subject IDs as an array
            $assigned_subject_ids = SectionSubject::where('section_id', $section->id)
                                    ->pluck('subject_id')
                                    ->toArray();

            $assigned_subject_ids = array_map('intval', $assigned_subject_ids);


            // Build the query for subjects within the same academic class
            $subjects = Subject::
                        where('academic_class_id', $academic_class_id)->
                        whereNotIn('id', $assigned_subject_ids)->
                        get();

            return response()->json($subjects);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */


    public function addSubject(Request $request){
        try {

            $request->validate([
                'sectionId' => "required|exists:sections,slug",
                'subjects' => "required|array",
            ]);

            $section = Section::where('slug',$request->sectionId)->firstOrFail();

            $section_subjects = [];
            foreach ($request->subjects as $subject_id) {

                $subject = Subject::where('slug',$subject_id
                )->firstOrFail();
                $section_subjects[] = [
                    "section_id" => $section->id,
                    "subject_id" => $subject->id,
                    "created_at" => now(),
                    "updated_at" => now()
                ];
            }

            $sec_sub = SectionSubject::insert($section_subjects);

            return response()->json($sec_sub, 200);
        } catch (Exception $e) {

            return $this->handleException($e, 'Failed to create subject');
        }
    }
}
