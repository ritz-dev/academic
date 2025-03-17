<?php

namespace App\Http\Controllers\APIs;

use Exception;
use App\Models\Section;
use App\Models\TimeTable;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Database\Seeders\TimeTableSeeder;
use App\Http\Resources\TimeTableResource;
use Illuminate\Validation\ValidationException;

class TimeTableController extends Controller
{
    public function list(Request $request)
    {
        try {
            $limit = (int) $request->limit;
            $search = $request->search;

            $section = Section::where('slug',$request->sectionId)->first();
            $query = TimeTable::where('section_id',$section->id)->orderBy('id', 'desc');

            if ($search) {
                $query->where('title', 'LIKE', $search . '%');
            }

            $data = $limit ? $query->paginate($limit) : $query->get();

            $data = TimeTableResource::collection($data);

            $total = TimeTable::count();

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
                'title' => ['required', 'array'],
                'title.label' => ['required', 'string'],
                'title.value' => ['required', 'string'],
                'academicClassId' => 'required|exists:academic_classes,id',
                'sectionId' => 'required|exists:sections,id',
                'subjectId' => 'nullable|exists:subjects,id',
                'teacherId' => 'nullable',
                'room' => 'nullable',
                'date' => 'required|date_format:Y-m-d',
                'startTime' => 'nullable|date_format:H:i',
                'endTime' => 'nullable|date_format:H:i|after:startTime',
                'type' => 'required|in:Lecture,Holiday,Exam,Break-Time',

                Rule::unique('time_tables')
                    ->where(fn ($query) => $query
                        ->where('academic_class_id', $request->academicClassId)
                        ->where('section_id', $request->sectionId)
                        ->where('subject_id', $request->subjectId)
                        ->where('teacher_id', $request->teacherId)
                        ->where('date', $request->date)
                        ->where('start_time', $request->startTime)
                        ->where('end_time', $request->endTime)
                    ),
            ]);

            $time_table = new TimeTable;
            $time_table->slug = Str::uuid();
            $time_table->title = $request->title['label'];
            $time_table->academic_class_id = $request->academicClassId;
            $time_table->section_id = $request->sectionId;
            $time_table->subject_id = $request->subjectId;
            $time_table->teacher_id = $request->teacherId;
            $time_table->room = $request->room;
            $time_table->date = $request->date;
            $time_table->start_time = $request->startTime;
            $time_table->end_time = $request->endTime;
            $time_table->type = $request->type;
            $time_table->save();

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
                'slug' => 'required|exists:time_tables,slug'
            ]);

            $data = TimeTable::where('slug',$request->slug)->firstOrFail();
            $academic_class = new TimeTableResource($data);
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
        try {

            $data = TimeTable::where('slug',$request->slug)->firstOrFail();

            $request->validate([
                'title' => ['required', 'array'], // Ensure title is an array (object in JSON)
                'title.label' => ['required', 'string'], // Validate label property
                'title.value' => ['required', 'string'],
                'academicClassId' => 'required|exists:academic_classes,id',
                'sectionId' => 'required|exists:sections,id',
                'subjectId' => 'required|exists:subjects,id',
                'teacherId' => 'required',
                'room' => 'required',
                'date' => 'required|date_format:Y-m-d',
                'startTime' => 'required|date_format:H:i',
                'endTime' => 'required|date_format:H:i|after:startTime',
                'type' => 'required|in:Lecture,Holiday,Exam',
                Rule::unique('time_tables')
                    ->where(fn ($query) => $query
                        ->where('academic_class_id', $request->academicClassId)
                        ->where('section_id', $request->sectionId)
                        ->where('subject_id', $request->subjectId)
                        ->where('teacher_id', $request->teacherId)
                        ->where('date', $request->date)
                        ->where('start_time', $request->startTime)
                        ->where('end_time', $request->endTime)
                    )
                    ->ignore($data->id),
            ]);


            $data->title = $request->title['label'];
            $data->academic_class_id = $request->academicClassId;
            $data->section_id = $request->sectionId;
            $data->subject_id = $request->subjectId;
            $data->teacher_id = $request->teacherId;
            $data->room = $request->room;
            $data->date = $request->date;
            $data->start_time = $request->startTime;
            $data->end_time = $request->endTime;
            $data->type = $request->type;
            $data->save();

            $timetable = new TimeTableResource($data);

            return response()->json($timetable, 200);
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

    public function updateDateTime(Request $request) {

        try {
            $request->validate([
                'date' => 'required|date_format:Y-m-d',
                'startTime' => 'required|date_format:H:i',
                'endTime' => 'required|date_format:H:i|after:startTime',
            ]);

            $data = TimeTable::where('slug',$request->slug)->firstOrFail();

            $existingTimeTable = TimeTable::where('date', $request->date)
                                            ->where('start_time', $request->startTime)
                                            ->where('end_time', $request->endTime)
                                            ->where('slug', '!=', $request->slug)
                                            ->exists();

            if ($existingTimeTable) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'A timetable with the same date and time already exists.',
                ], 400);
            }

            $data->date = $request->date;
            $data->start_time = $request->startTime;
            $data->end_time = $request->endTime;
            $data->save();

            $timetable = new TimeTableResource($data);

            return response()->json($timetable, 200);
        }catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while updating date and time.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $timetable = TimeTable::where('slug',$request->slug)->firstOrFail();
            $timetable->delete();

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
        try{
            $request->validate([
                'sectionId' => 'required|exists:sections,slug'
            ]);

            $section = Section::where('slug',$request->sectionId)->firstOrFail();

            $data = TimeTable::with(['academicClass','section','subject'])
                                    ->where('section_id',$section->id)
                                    ->get();

            $time_tables = TimeTableResource::collection($data);

            return response()->json($time_tables,200);
        }catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while showing.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function bySectionDate(Request $request)
    {
        try{
            $request->validate([
                'sectionId' => 'required|exists:sections,slug',
                'date' => 'required|date'
            ]);

            $section = Section::where('slug',$request->sectionId)->firstOrFail();

            $formattedDate = \Carbon\Carbon::parse($request->date)->format('Y-m-d');

            $data = TimeTable::where('section_id',$section->id)
                            ->whereDate('date', $formattedDate)
                            ->whereNotIn('type', ['Holiday', 'Break-Time'])
                            ->select(['slug','title as name'])
                            ->get();

            return response()->json($data,200);
        }catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while showing.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
