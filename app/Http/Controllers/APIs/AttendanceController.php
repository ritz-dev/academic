<?php

namespace App\Http\Controllers\APIs;

use Exception;
use Carbon\Carbon;
use App\Models\TimeTable;
use App\Models\Attendance;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\BlockChainService;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    protected $blockchainService;

    public function __construct(BlockChainService $blockchainService)
    {
        $this->blockchainService = $blockchainService;
    }

    public function getAttendance()
    {
        try {
            $attendance =  Attendance::with(['timetable'])->get();
            return response()->json(AttendanceResource::collection($attendance), 200);
        }catch (Exception $e) {
            return $this->handleException($e, 'Failed to fetch attendance');
        }
    }

    public function recordAttendance(Request $request)
    {
        try {
            $request->validate([
                'timetableId' => 'required|exists:time_tables,id',
                'attendeeId' => 'required|string',
                'attendeeType' => 'required|in:student,teacher',
                'status' => 'required|in:present,absent,late',
                'date' => 'required|date',
                'remarks' => 'nullable|string',
            ]);

            $previousHash = $this->blockchainService->getPreviousHash(Attendance::class);
            $timestamp = now();
            $calculatedHash = $this->blockchainService->calculateHash(
                $previousHash,
                json_encode($request->all()),
                $timestamp->format('Y-m-d H:i:s')
            );

            $attendance = new Attendance;
            $attendance->slug = Str::uuid();
            $attendance->timetable_id = $request->timetableId;
            $attendance->attendee_id = $request->attendeeId;
            $attendance->attendee_type = $request->attendeeType;
            $attendance->status = $request->status;
            $attendance->date = $request->date;
            $attendance->previous_hash = $previousHash;
            $attendance->hash = $calculatedHash;
            $attendance->remarks = $request->remarks;
            $attendance->save();

            return response()->json($attendance,200);
        }catch (Exception $e) {
            return $this->handleException($e, 'Failed to create attendance');
        }
    }

    public function verifyAttendance(Request $request)
    {
        // Find the Attendance by its ID
        $attendance = Attendance::findOrFail($request->id);

        if (!$attendance) {
            return response()->json(['message' => 'Attendance not found'], 404);
        }

        // Convert timestamp to Carbon instance if it's stored as a string
        $timestamp = Carbon::parse($attendance->timestamp);

        // Recalculate hash based on Attendance data
        $calculatedHash = $this->blockchainService->calculateHash(
            $attendance->previous_hash,
            json_encode($attendance),
            $timestamp->format('Y-m-d H:i:s') // Ensure timestamp is formatted correctly
        );

        // Compare the calculated hash with the stored hash
        if ($calculatedHash !== $attendance->hash) {
            return response()->json(['message' => 'Attendance has been tampered with'], 400);
        }

        // Check if the previous hash matches the previous Attendance's hash (if it exists)
        if ($attendance->previous_hash === '0000000000000000000000000000000000000000000000000000000000000000') {
            return response()->json(['message' => 'Attendance is valid and verified'], 200);
        }

        $previousAttendance = Attendance::where('hash', $attendance->previous_hash)->first();

        if ($previousAttendance) {
            return response()->json(['message' => 'Attendance is valid and verified'], 200);
        }

        return response()->json(['message' => 'Invalid Attendance chain'], 400);
    }

    public function create(Request $request)
    {
        try{

            $timetable = TimeTable::where('slug',$request->timetableId)->first();

            $request->validate([
                'timetableId' => 'required|string',
                'attendeeIds' => 'required|array',
                'attendeeType' => 'required|string',
                'status' => 'in:present,absent,late',
                Rule::unique('attendances') ->where(fn ($query) => $query
                ->where('timetable_id', $timetable->id)
                ->whereIn('attendee_id', $request->attendeeIds)
                )
            ]);



            $attendee_ids = $request->attendeeIds;

            // $attendance_exist = Attendance::where('timetable_id', $timetable->id)
            //                                     ->whereIn('attendee_id', $attendee_ids)
            //                                     ->where('attendee_type', $request->attendeeType)
            //                                     ->where('status', $request->status)
            //                                     ->exists();

            // if ($attendance_exist) {
            //     return response()->json([
            //         "message" => "Attendance already exists in timetable."
            //     ], 400);
            // }

            foreach($attendee_ids as $attendee_id){

                $attendance = new Attendance;
                $attendance->slug = Str::uuid();
                $attendance->timetable_id = $timetable->id;
                $attendance->attendee_id = $attendee_id;
                $attendance->attendee_type = $request->attendeeType;
                $attendance->status = $request->status;
                $attendance->date = Carbon::parse($request->date);
                $attendance->save();
            }

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

    public function bySchedule(Request $request)
    {
        try{
            $request->validate([
                "timetableId" => "required|string",
                "attendeeType" => "required|string"
            ]);

            $timetable = TimeTable::where('slug',$request->timetableId)->first();

            $attendance = Attendance::where('timetable_id',$timetable->id)
                                    ->where('attendee_type',$request->attendeeType)
                                    ->get();

            $attendance = AttendanceResource::collection($attendance);

            return response()->json($attendance, 200);
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

}
