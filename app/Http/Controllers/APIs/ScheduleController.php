<?php

namespace App\Http\Controllers\APIs;

use Carbon\Carbon;
use App\Models\Section;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Models\WeeklySchedule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


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

    public function weekly(Request $request)
    {
        $request->validate([
            'sectionId' => 'required|string',
        ]);

        $section = Section::where('slug', $request->sectionId)->firstOrFail();

        $weeklySchedule = WeeklySchedule::where('section_id', $section->id)->get()->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'section_id' => $schedule->section_id,
                'day' => $schedule->day,
                'startTime' => $schedule->start_time,
                'endTime' => $schedule->end_time,
                'isBreak' => $schedule->is_break,
                'title' => $schedule->title,
                'created_at' => $schedule->created_at,
                'updated_at' => $schedule->updated_at,
            ];
        });

        return response()->json($weeklySchedule);
    }

    public function create(Request $request)
    {

        $request->validate([
            'sectionId' => 'required|string',
            'date' => 'required|date',
            'type' => 'required|string',
            'startTime' => 'required|string',
            'endTime' => 'required|string',
        ]);

        $section = Section::with(['academicClass','academicClass.academicYear'])->where('slug', $request->sectionId)->firstOrFail();

        $day = date('N', strtotime($request->date));

        $dateArray = $this->getDaysBetweenDates($request->date, $section->academicClass->academicYear->start_date, $section->academicClass->academicYear->end_date);

        DB::beginTransaction();

        try {
            if ($request->type === 'holiday') {
                // Create Weekly Schedule
                $schedule = WeeklySchedule::create([
                    'title' => 'Holiday',
                    'section_id' => $section->id,
                    'subject_id' => null,
                    'day' => $day,
                    'start_time' => null,
                    'end_time' => null,
                    'is_break' => true,
                ]);
    
                // Create Schedules for each date
                foreach ($dateArray as $date) {
                    Schedule::create([
                        'section_id' => $section->id,
                        'weekly_schedule_id' => $schedule->id,
                        'date' => $date,
                        'type' => $request->type,
                    ]);
                }
                
            }else if ($request->type === 'break-time') {
                // Create Weekly Schedule
                $schedule = WeeklySchedule::create([
                    'title' => 'Break-Time',
                    'section_id' => $section->id,
                    'subject_id' => null,
                    'day' => $day,
                    'start_time' => $request->startTime,
                    'end_time' => $request->endTime,
                    'is_break' => true,
                ]);

                // Create Schedules for each date
                foreach ($dateArray as $date) {
                    Schedule::create([
                        'section_id' => $section->id,
                        'weekly_schedule_id' => $schedule->id,
                        'date' => $date,
                        'type' => $request->type,
                    ]);
                }
            }

            DB::commit(); // Commit Transaction

            return response()->json([
                'success' => true,
            ]);

        } catch (\Exception $e) {

            DB::rollBack(); // Rollback Transaction if an error occurs
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDaysBetweenDates($date, $startDate, $endDate)
    {
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        $dayName = ucfirst(strtolower($date));
        
        $dayNumber = Carbon::parse($dayName)->dayOfWeekIso;

        $days = [];

        // Loop through date range
        while ($startDate->lte($endDate)) {
            if ($startDate->dayOfWeekIso == $dayNumber) {
                $days[] = $startDate->toDateString();
            }
            $startDate->addDay(); // Increment date by 1 day
        }

        return $days;
    }
}
