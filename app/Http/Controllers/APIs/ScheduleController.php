<?php

namespace App\Http\Controllers\APIs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Http\Controllers\APIs\JsonResponse;

class ScheduleController extends Controller
{
    public function list()
    {
        $schedules = Schedule::with('weeklySchedule')->get();

        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }

    public function create()
    {
        // $validated = $request->validate([
        //     'date' => 'required|date',
        //     'startTime' => 'required|string',
        //     'endTime' => 'required|string',
        //     'room' => 'nullable|string',
        //     'sectionId' => 'required|array',
        //     'subject' => 'nullable|string',
        //     'teacher' => 'nullable|string',
        //     'title' => 'nullable|string',
        //     'type' => 'required|string',
        // ]);
        $validator = Validator::make($request->all(), [
            'sectionId' => 'required|exists:sections,id',  // Ensure section exists in 'sections' table
            'date' => 'required|date',  // Validate the date format (YYYY-MM-DD)
            'startTime' => 'required|date_format:H:i',  // Validate time format (HH:mm)
            'endTime' => 'required|date_format:H:i|after:start_time',  // Ensure end time is after start time
            'type' => 'required|string',
            'isBreak' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }
}
