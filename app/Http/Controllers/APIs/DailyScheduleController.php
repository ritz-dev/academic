<?php

namespace App\Http\Controllers\APIs;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\DailySchedule;
use App\Models\WeeklySchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\AcademicClassSection;

class DailyScheduleController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|exists:academic_class_sections,slug',
        ]);

        try {
            DB::beginTransaction();

            $weeklyHolidays = ['Saturday', 'Sunday'];

            // Fetch section with academic year
            $section = AcademicClassSection::with('academicYear')->where('slug', $validated['slug'])->firstOrFail();

            $startDate = Carbon::parse($section->academicYear->start_date)->startOfDay();
            $endDate = Carbon::parse($section->academicYear->end_date)->endOfDay();
            
            $currentDate = $startDate->copy();

            // Optional: prevent duplicates
            DailySchedule::where('academic_class_section_slug', $section->slug)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->delete();

            while ($currentDate->lte($endDate)) {
                $dayName = $currentDate->format('l'); // E.g., "Monday"
                $isWeeklyHoliday = in_array($dayName, $weeklyHolidays);

                if ($isWeeklyHoliday) {
                    DailySchedule::create([
                        // 'slug' => (string) Str::uuid(),
                        'date' => $currentDate->toDateString(),
                        'academic_class_section_slug' => $section->slug,
                        'subject_id' => null,
                        'teacher_id' => null,
                        'start_time' => null,
                        'end_time' => null,
                        'type' => 'break',
                        'is_holiday' => true,
                        'holiday_type' => 'weekly',
                        'note' => 'Weekly Holiday',
                        'academic_info' => 'info'
                    ]);
                } else {
                    $weeklySlots = WeeklySchedule::where('academic_class_section_slug', $section->slug)
                        ->where('day_of_week', $dayName)
                        ->get();

                    foreach ($weeklySlots as $slot) {
                        DailySchedule::create([
                            // 'slug' => (string) Str::uuid(),
                            'date' => $currentDate->toDateString(),
                            'academic_class_section_slug' => $section->slug,
                            'subject_id' => $slot->subject_id,
                            'teacher_id' => $slot->teacher_id,
                            'start_time' => $slot->start_time,
                            'end_time' => $slot->end_time,
                            'type' => $slot->type,
                            'is_holiday' => false,
                            'note' => null,
                            'academic_info' => 'info'
                        ]);
                    }
                }

                $currentDate->addDay();
            }

            DB::commit();

            return response()->json([
                'message' => 'Daily schedules generated successfully.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate daily schedules: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to generate daily schedules.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function bySection(Request $request)
    {
        // Validate the request if needed
        $request->validate([
            'slug'  => 'required'
        ]);

        try{

        $section = AcademicClassSection::where('slug', $request->slug)->first();

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found'
            ], 404);
        }

        $schedules = DailySchedule::where('academic_class_section_slug', $section->slug)->get();

        if ($schedules->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No schedules found for this section'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function byTeacherAcademicYear(Request $request)
    {
        // Validate the request if needed
        $request->validate([
            // 'teacher_id'  => 'required|string|max:255',
            // 'academic_year_id'  => 'required|academic_years.id'
        ]);

        try{

            $sectionIds = AcademicClassSection::where('academic_year_id', $request->academic_year_id)->pluck('id');

            if ($sectionIds->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No sections found for the specified academic year.'
                ], 404);
            }


            $schedules = DailySchedule::with([
                'academicClassSection.academicYear',
                'academicClassSection.class',
                'academicClassSection.section',
                'subject'
            ])->whereIn('academic_class_section_id', $sectionIds)
                        ->where('teacher_id', $request->teacher_id)
                        ->orderBy('date', 'desc')
                        ->take(10)
                        ->get();

            if ($schedules->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedules found for this teacher in the specified academic year.'
                ], 404);
            }

            Log::info('Schedules retrieved', ['schedules' => $schedules]);

            return response()->json([
                'success' => true,
                'data' => $schedules
            ]);
        
        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
