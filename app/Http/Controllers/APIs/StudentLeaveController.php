<?php

namespace App\Http\Controllers\APIs;

use App\Models\StudentLeave;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StudentLeaveController extends Controller
{
    public function index(Request $request)
    {
        try {

            $validate = $request->validate([
                'academic_class_section_slug' => 'nullable|string',
                'student_enrollment_slug' => 'nullable|string',
                'weekly_schedule_slug' => 'nullable|string',
                'date' => 'nullable|date',
                'leave_type' => 'nullable|in:sick,personal,vacation,emergency,other',
                'status' => 'nullable|in:approved,rejected,pending',
                'limit' => 'nullable|integer|min:1|max:100',
                'skip' => 'nullable|integer|min:0',
            ]);

            $query = StudentLeave::query()
                ->when($validate['academic_class_section_slug'] ?? null, function ($q) use ($validate) {
                    return $q->where('academic_class_section_slug', $validate['academic_class_section_slug']);
                })
                ->when($validate['student_enrollment_slug'] ?? null, function ($q) use ($validate) {
                    return $q->where('student_enrollment_slug', $validate['student_enrollment_slug']);
                })
                ->when($validate['weekly_schedule_slug'] ?? null, function ($q) use ($validate) {
                    return $q->where('weekly_schedule_slug', $validate['weekly_schedule_slug']);
                })
                ->when($validate['date'] ?? null, function ($q) use ($validate) {
                    return $q->whereDate('date', $validate['date']);
                })
                ->when($validate['leave_type'] ?? null, function ($q) use ($validate) {
                    return $q->where('leave_type', $validate['leave_type']);
                })
                ->when($validate['status'] ?? null, function ($q) use ($validate) {
                    return $q->where('status', $validate['status']);
                });
            
            $total = (clone $query)->count();

            if (!empty($validate['limit'])) {
                $query->limit($validate['limit']);
            }
            if (!empty($validate['skip'])) {
                $query->skip($validate['skip']);
            }

            $leaves = $query->get();

            return response()->json([
                'status' => 'OK! The request was successful',
                'total' => $total,
                'data' => $leaves
            ], 200);
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
                'student_enrollment_slug' => ['required', 'string', 'exists:student_enrollments,slug'],
                'academic_class_section_slug' => ['required', 'string', 'exists:academic_class_sections,slug'],
                'weekly_schedule_slug' => ['nullable', 'string', 'exists:weekly_schedules,slug'],
                'student_name' => ['required', 'string'],
                'academic_info' => ['required', 'string'],
                'date' => ['required', 'date'],
                'leave_type' => ['required', 'in:sick,personal,vacation,emergency,other'],
                'reason' => ['nullable', 'string'],
                'status' => ['nullable', 'in:pending,approved,rejected'],
            ]);

            $leave = StudentLeave::create([
                'student_enrollment_slug' => $validated['student_enrollment_slug'],
                'academic_class_section_slug' => $validated['academic_class_section_slug'],
                'weekly_schedule_slug' => $validated['weekly_schedule_slug'],
                'student_name' => $validated['student_name'],
                'academic_info' => $validated['academic_info'],
                'date' => $validated['date'],
                'leave_type' => $validated['leave_type'],
                'reason' => $validated['reason'] ?? null,
                'status' => $validated['status'] ?? 'pending',
            ]);

            return response()->json([
                'status' => 'OK! The request was successful',
                'message' => 'Student leave created successfully',
                'data' => $leave
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        // Logic to show a specific student leave record
        return response()->json(['message' => 'Details of student leave', 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        // Logic to update a specific student leave record
        return response()->json(['message' => 'Student leave updated successfully', 'id' => $id]);
    }

    public function delete($id)
    {
        // Logic to delete a specific student leave record
        return response()->json(['message' => 'Student leave deleted successfully', 'id' => $id]);
    }
}
