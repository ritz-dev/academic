<?php

namespace App\Http\Controllers\APIs;

use Illuminate\Http\Request;
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

    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'weekly_schedule_slug' => ['nullable', 'string'],
                'academic_class_section_slug' => ['nullable', 'string', 'exists:academic_class_sections,slug'],
                'attendee_slug' => ['nullable', 'string'],
                'attendee_type' => ['nullable', 'string'],
                'status' => ['nullable', 'in:present,absent,late,excused'],
                'attendance_type' => ['nullable', 'in:class,exam,event'],
                'start_date' => ['nullable', 'date'],
                'end_date' => ['nullable', 'date'],
                'limit' => ['nullable', 'integer', 'min:1'],
                'skip' => ['nullable', 'integer', 'min:0'],
            ]);

            $query = AcademicAttendance::query()
                ->when(!empty($validated['weekly_schedule_slug']), fn($q) =>
                    $q->where('weekly_schedule_slug', $validated['weekly_schedule_slug']))
                ->when(!empty($validated['academic_class_section_slug']), fn($q) =>
                    $q->where('academic_class_section_slug', $validated['academic_class_section_slug']))
                ->when(!empty($validated['attendee_slug']), fn($q) =>
                    $q->where('attendee_slug', $validated['attendee_slug']))
                ->when(!empty($validated['attendee_type']), fn($q) =>
                    $q->where('attendee_type', $validated['attendee_type']))
                ->when(!empty($validated['status']), fn($q) =>
                    $q->where('status', $validated['status']))
                ->when(!empty($validated['attendance_type']), fn($q) =>
                    $q->where('attendance_type', $validated['attendance_type']));
                // ->when(!empty($validated['start_date']), fn($q) =>
                //     $q->whereDate('date', '>=', $validated['start_date']))
                // ->when(!empty($validated['end_date']), fn($q) =>
                //     $q->whereDate('date', '<=', $validated['end_date']))
                // ->orderByDesc('date');

            $total = $query->count();

            if (!empty($validated['skip'])) {
                $query->skip($validated['skip']);
            }
            if (!empty($validated['limit'])) {
                $query->take($validated['limit']);
            }

            $results = $query->get();

            return response()->json([
                'status' => 'OK! The request was successful',
                'total' => $total,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'attendances' => 'required|array|min:1',
                'attendances.*.attendee_type' => 'required|in:student,teacher',
                'attendances.*.attendee_slug' => 'required|string',
                'attendances.*.weekly_schedule_slug' => 'required|exists:weekly_schedules,slug',
                'attendances.*.subject' => 'required|string',
                'attendances.*.academic_class_section_slug' => 'required|string',
                'attendances.*.academic_info' => 'nullable|string',
                'attendances.*.status' => 'required|in:present,absent,late,excused',
                'attendances.*.remark' => 'nullable|string',
                'attendances.*.attendance_type' => 'nullable|in:class,exam,event',
                'attendances.*.date' => 'required|date',
            ]);

            $inserted = [];

            foreach ($validated['attendances'] as $item) {
                $inserted[] = AcademicAttendance::create([
                    'slug' => Str::uuid(), // or custom logic
                    'weekly_schedule_slug' => $item['schedule_slug'],
                    'subject' => $item['subject'],
                    'academic_class_section_slug' => $item['academic_class_section_slug'],
                    'academic_info' => $item['academic_info'] ?? null,
        
                    'attendee_slug' => $item['attendee_slug'],
                    'attendee_name' => $this->getAttendeeName($item['attendee_slug'], $item['attendee_type']), // Optional helper
                    'attendee_type' => $item['attendee_type'],
                    'status' => $item['status'],
                    'attendance_type' => $item['attendance_type'] ?? 'class',
        
                    'date' => $item['date'],
                    'modified' => now(),
                    'modified_by' => auth()->user()?->name ?? 'system',
                    'remark' => $item['remark'] ?? null,
        
                    'previous_hash' => null,
                    'hash' => null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to record attendance.',
                'error' => $e->getMessage()
            ], 500);
        }
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
}
