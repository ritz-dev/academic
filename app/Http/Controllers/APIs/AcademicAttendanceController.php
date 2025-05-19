<?php

namespace App\Http\Controllers\APIs;

use Illuminate\Http\Request;
use App\Models\DailySchedule;
use App\Models\AcademicAttendance;
use App\Http\Controllers\Controller;


class AcademicAttendanceController extends Controller
{
    public function bySchedule(Request $request)
    {
        // Validate the request if needed
        $request->validate([
            'slug'  => 'required'
        ]);

        $schedule = DailySchedule::where('slug', $request->slug)->firstOrFail();

        $attendances = AcademicAttendance::where('schedule_id', $schedule->id)->get();

        return response()->json([
            'success' => true,
            'data' => $attendances
        ]);
    }
}
