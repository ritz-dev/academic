<?php

namespace App\Http\Controllers\APIs;

use Illuminate\Http\Request;
use App\Models\DailySchedule;
use App\Models\AcademicAttendance;
use Illuminate\Support\Facades\DB;
use App\Services\BlockChainService;
use App\Http\Controllers\Controller;


class AcademicAttendanceController extends Controller
{
    public function __construct(BlockChainService $blockchainService)
    {
        $this->blockchainService = $blockchainService;
    }

    public function store(Request $request)
    {
        // Validate the request if needed
        $validated = $request->validate([
            'attendances' => 'required|array|min:1',
            'attendances.*.attendee_type' => 'required|in:student,teacher',
            'attendances.*.attendee_slug' => 'required|string',
            'attendances.*.schedule_slug' => 'required|exists:daily_schedules,slug',
            'attendances.*.status' => 'required|in:present,absent,late,excused',
            'attendances.*.remark' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['attendances'] as $attendance) {
                $previousHash = $this->blockchainService->getPreviousHash(AcademicAttendance::class);
                $timestamp = now();
                $calculatedHash = $this->blockchainService->calculateHash(
                    $previousHash,
                    json_encode($attendance),
                    $timestamp->format('Y-m-d H:i:s')
                );
    
                AcademicAttendance::create([
                    'previous_hash' => $previousHash,
                    'hash' => $calculatedHash,
                    'attendee_type' => $attendance['attendee_type'],
                    'attendee_slug' => $attendance['attendee_slug'],
                    'schedule_slug' => $attendance['schedule_slug'],
                    'status' => $attendance['status'],
                    'date' => $timestamp,
                    'remark' => $attendance['remark'] ?? null,
                ]);
            }
    
            DB::commit();
    
            return response()->json([
                'message' => 'Attendance recorded successfully.',
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to record attendance.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bySchedule(Request $request)
    {
        // Validate the request if needed
        $request->validate([
            'slug'  => 'required'
        ]);

        $schedule = DailySchedule::where('slug', $request->slug)->firstOrFail();

        $attendances = AcademicAttendance::where('schedule_slug', $schedule->slug)->get();

        return response()->json([
            'success' => true,
            'data' => $attendances
        ]);
    }
}
