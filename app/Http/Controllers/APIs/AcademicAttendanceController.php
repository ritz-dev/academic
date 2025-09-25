<?php

namespace App\Http\Controllers\APIs;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\AcademicAttendance;
use Illuminate\Support\Facades\DB;
use App\Services\BlockChainService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;


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
                'start_time' => ['nullable', 'date_format:H:i'],
                'end_time' => ['nullable', 'date_format:H:i'],
                'limit' => ['nullable', 'integer', 'min:1'],
                'skip' => ['nullable', 'integer', 'min:0'],
            ]);

            $query = AcademicAttendance::with(['weeklySchedule'])
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
                    $q->where('attendance_type', $validated['attendance_type']))
                ->when(!empty($validated['start_date']) && !empty($validated['end_date']), function ($q) use ($validated) {
                    $startInt = (int) Carbon::parse($validated['start_date'])->format('Ymd');
                    $endInt = (int) Carbon::parse($validated['end_date'])->format('Ymd');
                    $q->whereBetween('date', [$startInt, $endInt]);
                })
                ->when(!empty($validated['start_date']) && empty($validated['end_date']), function ($q) use ($validated) {
                    $startInt = (int) Carbon::parse($validated['start_date'])->format('Ymd');
                    $q->where('date', '>=', $startInt);
                })
                ->when(!empty($validated['end_date']) && empty($validated['start_date']), function ($q) use ($validated) {
                    $endInt = (int) Carbon::parse($validated['end_date'])->format('Ymd');
                    $q->where('date', '<=', $endInt);
                })
                ->when(!empty($validated['start_time']), function ($q) use ($validated) {
                    $q->whereHas('weeklySchedule', function ($query) use ($validated) {
                        $query->where('start_time', '>=', $validated['start_time']);
                    });
                })
                ->when(!empty($validated['end_time']), function ($q) use ($validated) {
                    $q->whereHas('weeklySchedule', function ($query) use ($validated) {
                        $query->where('end_time', '<=', $validated['end_time']);
                    });
                })
                ->orderByDesc('date');

           $total = (clone $query)->count();

            if (!empty($validated['skip'])) {
                $query->skip($validated['skip']);
            }
            if (!empty($validated['limit'])) {
                $query->take($validated['limit']);
            }

            $results = $query->get();

            $grouped = $results->groupBy('attendee_type')->map(function ($items) {
                return $items->pluck('attendee_slug')->unique()->values()->all();
            });

            $attendeeData = [];

            foreach ($grouped as $type => $slugs) {
                $baseUrl = config('services.user_management.url');
                $endpoint = match ($type) {
                    'student' => "$baseUrl" . "students",
                    'teacher' => "$baseUrl" . "teachers",
                    default => null,
                };
    
                if (!$endpoint) continue;

                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    // 'Authorization' => $request->header('Authorization'),
                ])->post($endpoint, ['slugs' => $slugs]);
    
                if ($response->successful()) {
                    $attendeeData[$type] = collect($response->json('data'))->keyBy('slug')->toArray();
                }
            }

            $results = $results->map(function ($item) use ($attendeeData) {
                $attendee = $attendeeData[$item->attendee_type][$item->attendee_slug] ?? null;
                $data = $item->toArray();

                $data['date'] = Carbon::createFromFormat('Ymd', $item->date)->toDateString();
                $data['attendee'] = $attendee;
                
                return $data;
            });

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
                'attendances.*.attendee_name' => 'nullable|string',
                'attendances.*.subject' => 'required|string',
                'attendances.*.academic_class_section_slug' => 'required|string',
                'attendances.*.academic_info' => 'nullable|string',
                'attendances.*.status' => 'required|in:present,absent,late,excused',
                'attendances.*.remark' => 'nullable|string',
                'attendances.*.attendance_type' => 'nullable|in:class,exam,event',
                'attendances.*.date' => 'required|date',
                'attendances.*.approved_slug' => 'nullable|string', // Assuming no approval slug is provided
            ]);

            $inserted = [];

            DB::beginTransaction();

            foreach ($validated['attendances'] as $item) {
                $previousHash = $this->blockchainService->getPreviousHash(AcademicAttendance::class);
                $timestamp = now();
                $calculatedHash = $this->blockchainService->calculateHash(
                    $previousHash,
                    json_encode($item),
                    $timestamp->format('Y-m-d H:i:s')
                );

                $dateInput = $item['date']; 
                $formattedDate = (int) Carbon::parse($dateInput)->format('Ymd');

                $inserted[] = AcademicAttendance::create([
                    'weekly_schedule_slug' => $item['weekly_schedule_slug'],
                    'subject' => $item['subject'],
                    'academic_class_section_slug' => $item['academic_class_section_slug'],
                    'academic_info' => $item['academic_info'] ?? null,
        
                    'attendee_slug' => $item['attendee_slug'],
                    'attendee_name' => $item['attendee_name'],
                    'attendee_type' => $item['attendee_type'],
                    'status' => $item['status'],
                    'attendance_type' => $item['attendance_type'],
                    'approved_slug' => $item['approved_slug'] ?? null,
        
                    'date' => $formattedDate,
                    'modified' => null,
                    'modified_by' => null,
                    'remark' => $item['remark'] ?? null,
        
                    'previous_hash' => $previousHash,
                    'hash' => $calculatedHash,
                ]);
            }
            DB::commit();

            return response()->json([
                'message' => 'Attendances recorded successfully.',
                'data' => $inserted
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to record attendance.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:academic_attendances,slug',
            ]);

            $attendance = AcademicAttendance::where('slug', $validated['slug'])->with(['weeklySchedule', 'academicClassSection'])->firstOrFail();

            if (!$attendance) {
                return response()->json([
                    'message' => 'Attendance not found.',
                ], 404);
            }

            $baseUrl = config('services.user_management.url');
            $attendeeData = null;
    
            // Only proceed if attendee_type and attendee_slug are set
            if ($attendance->attendee_type && $attendance->attendee_slug) {
                $endpoint = match ($attendance->attendee_type) {
                    'student' => "{$baseUrl}students/show",
                    'teacher' => "{$baseUrl}teachers/show",
                    default => null,
                };
                
                if ($endpoint) {
                    $response = Http::withHeaders([
                        'Accept' => 'application/json',
                        // Optional: include auth
                        // 'Authorization' => $request->header('Authorization'),
                    ])->post($endpoint, [
                        'slug' => $attendance->attendee_slug,
                    ]);

                    if ($response->successful()) {
                        $fetched = $response->json('data');
                        $attendeeData = $fetched;
                    }
                }
            }

            $attendance->date = Carbon::createFromFormat('Ymd', $attendance->date)->toDateString();
            $attendance->attendee = $attendeeData;

            return response()->json([
                'message' => 'Attendance retrieved successfully.',
                'data' => $attendance
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve attendance.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:academic_attendances,slug',
                'attendee_type' => 'required|in:student,teacher',
                'attendee_slug' => 'required|string',
                'weekly_schedule_slug' => 'required|exists:weekly_schedules,slug',
                'attendee_name' => 'nullable|string',
                'subject' => 'required|string',
                'academic_class_section_slug' => 'required|string',
                'academic_info' => 'nullable|string',
                'status' => 'required|in:present,absent,late,excused',
                'remark' => 'nullable|string',
                'attendance_type' => 'nullable|in:class,exam,event',
                'approved_slug' => 'nullable|string', // Assuming no approval slug is provided
                'date' => 'required|date',
            ]);

            $attendance = AcademicAttendance::where('slug', $validated['slug'])->firstOrFail();

            if (!$attendance) {
                return response()->json([
                    'message' => 'Attendance not found.',
                ], 404);
            }

            DB::beginTransaction();

            $previousHash = $this->blockchainService->getPreviousHash(AcademicAttendance::class);
            $timestamp = now();
            $calculatedHash = $this->blockchainService->calculateHash(
                $previousHash,
                json_encode($validated),
                $timestamp->format('Y-m-d H:i:s')
            );

            $dateInput = $request->input('date'); 
            $formattedDate = (int) Carbon::parse($dateInput)->format('Ymd');

            $attendance->update([
                'weekly_schedule_slug' => $validated['weekly_schedule_slug'],
                'subject' => $validated['subject'],
                'academic_class_section_slug' => $validated['academic_class_section_slug'],
                'academic_info' => $validated['academic_info'] ?? null,

                'attendee_slug' => $validated['attendee_slug'],
                'attendee_name' => $validated['attendee_name'],
                'attendee_type' => $validated['attendee_type'],
                'status' => $validated['status'],
                'attendance_type' => $validated['attendance_type'] ?? 'class',
                'approved_slug' => $validated['approved_slug'] ?? null,

                'date' => $formattedDate,
                'modified' => $timestamp,
                'modified_by' => auth()->user()?->name ?? 'system',
                'remark' => $validated['remark'] ?? null,

                'previous_hash' => $previousHash,
                'hash' => $calculatedHash,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Attendance updated successfully.',
                'data' => $attendance
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update attendance.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handleAction(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:academic_attendances,slug',
                'status' => 'required|string|in:present,absent,late,excused',
            ]);

            $attendance = AcademicAttendance::where('slug', $validated['slug'])->firstOrFail();

            if (!$attendance) {
                return response()->json([
                    'message' => 'Attendance not found.',
                ], 404);
            }

            DB::beginTransaction();

            switch ($validated['status']) {
                case 'present':
                    $attendance->status = 'present';
                    break;
                case 'absent':
                    $attendance->status = 'absent';
                    break;
                case 'late':
                    $attendance->status = 'late';
                    break;
                case 'excused':
                    $attendance->status = 'excused';
                    break;
            }

            $attendance->save();

            DB::commit();

            return response()->json([
                'message' => "Attendance marked as {$attendance->status} successfully.",
                'data' => $attendance
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to perform action on attendance.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:academic_attendances,slug',
            ]);
            DB::beginTransaction();

            $attendance = AcademicAttendance::where('slug', $validated['slug'])->firstOrFail();

            if (!$attendance) {
                return response()->json([
                    'message' => 'Attendance not found.',
                ], 404);
            }
            $attendance->delete();

            DB::commit();

            return response()->json([
                'message' => 'Attendance deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete attendance.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
