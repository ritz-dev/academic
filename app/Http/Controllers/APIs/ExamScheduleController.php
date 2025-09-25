<?php

namespace App\Http\Controllers\APIs;

use Exception;
use Illuminate\Support\Str;
use App\Models\ExamSchedule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ExamStudentAssignment;
use App\Models\ExamTeacherAssignment;
use App\Http\Resources\ExamScheduleResource;
use Illuminate\Validation\ValidationException;

class ExamScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function list(Request $request)
    {
        try {
            $limit = (int) $request->limit;
            $search = $request->search;

            $query = ExamSchedule::orderBy('id', 'desc');

            if ($search) {
                $query->where('subject', 'LIKE', $search . '%');
            }

            $data = $limit ? $query->paginate($limit) : $query->get();

            $data = ExamScheduleResource::collection($data);

            $total = ExamSchedule::count();

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
        try{
            $request->validate([
                'examId' => 'required|exists:exams,id',
                'sectionId' => 'required|exists:sections,id',
                'subject' => 'required|string',
                'date'=> 'required|date',
                'startTime' =>'required|date_format:H:i',
                'endTime' => 'required|date_format:H:i|after:startTime',
                Rule::unique('exam_schedules')
                    ->where(fn ($query) => $query
                    ->where('subject', $request->subject)
                    ->where('date', $request->date)
                    ->where('start_time',$request->startTime)
                    ->where('end_time',$request->endTime)
                )
            ]);

            $exam_schedule = new ExamSchedule;
            $exam_schedule->slug = Str::uuid();
            $exam_schedule->exam_id = $request->examId;
            $exam_schedule->section_id = $request->sectionId;
            $exam_schedule->subject = $request->subject;
            $exam_schedule->date = $request->date;
            $exam_schedule->start_time = $request->startTime;
            $exam_schedule->end_time = $request->endTime;
            $exam_schedule->save();

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
                'slug' => 'required|exists:exam_schedules,slug'
            ]);

            $data = ExamSchedule::where('slug',$request->slug)->firstOrFail();
            $exam_schedule = new ExamScheduleResource($data);
            return response()->json($exam_schedule,200);

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
            $exam_schedule = ExamSchedule::where('slug',$request->slug)->firstOrFail();
            $request->validate([
                'examId' => 'required|exists:exams,id',
                'sectionId' => 'required|exists:sections,id',
                'subject' => 'required|string',
                'date'=> 'required|date',
                'startTime' =>'required|date_format:H:i',
                'endTime' => 'required|date_format:H:i|after:startTime',
                Rule::unique('exam_schedules')
                    ->where(fn ($query) => $query
                    ->where('subject', $request->subject)
                    ->where('date', $request->date)
                    ->where('start_time',$request->startTime)
                    ->where('end_time',$request->endTime)
                )->ignore($exam_schedule->id)
            ]);


            $exam_schedule->exam_id = $request->examId;
            $exam_schedule->section_id = $request->sectionId;
            $exam_schedule->subject = $request->subject;
            $exam_schedule->date = $request->date;
            $exam_schedule->start_time = $request->startTime;
            $exam_schedule->end_time = $request->endTime;
            $exam_schedule->save();

            $exam_schedule = new ExamScheduleResource($exam_schedule);

            return response()->json($exam_schedule,200);

        }catch (ValidationException $e) {
            return response()->json([
                'status' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while updating.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $exam_schedule = ExamSchedule::where('slug',$request->slug)->firstOrFail();
            $exam_schedule->delete();

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
