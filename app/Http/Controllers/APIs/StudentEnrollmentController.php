<?php

namespace App\Http\Controllers\APIs;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\StudentEnrollment;
use App\Http\Controllers\Controller;
use App\Models\AcademicClassSection;
use Illuminate\Support\Facades\Http;

class StudentEnrollmentController extends Controller
{
    public function byAcademicYear(Request $request)
    {
        $request->validate([
            'slug' => 'required',
        ]);
    
        // Get the section using the slug
        $section = AcademicClassSection::where('slug', $request->slug)->firstOrFail();
    
        // Get all enrollments in that section
        $enrollments = StudentEnrollment::where('academic_class_section_id', $section->id)->get();
    
        // Extract all student IDs
        $studentIds = $enrollments->pluck('student_id')->toArray();
    
        // Send POST request to fetch student details
        $studentsApiUrl = config('services.user_management.url') . 'students/enrollment';
    
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            // 'Authorization' => $request->header('Authorization'), // Uncomment if needed
        ])->post($studentsApiUrl, [
            'student_ids' => $studentIds,
        ]);
    
        if (!$response->ok()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch student info from user management service.',
            ], $response->status());
        }
    
        // Assume the API returns an array keyed by student_id
        $studentInfoMap = collect($response->json())->keyBy('slug');
    
        // Merge student info with enrollments
        $students = $enrollments->map(function ($enrollment) use ($studentInfoMap) {
            return [
                'enrollment' => $enrollment,
                'student_info' => $studentInfoMap->get($enrollment->student_id),
            ];
        });
    
        return response()->json([
            'success' => true,
            'data' => $students,
        ]);
    }

    public function byStudent (Request $request)
    {
        $request->validate([
            'student_id' => 'required|string|max:255',
        ]);
    
        // Get all enrollments for that student
        $enrollments = StudentEnrollment::where('student_id', $request->student_id)->get();

        if($enrollments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No enrollments found for this student.',
            ], 404);
        }
    
        // Extract all student IDs
        $studentIds = $enrollments->pluck('student_id')->toArray();
    
        // Send POST request to fetch student details
        $studentsApiUrl = config('services.user_management.url') . 'students/enrollment';
    
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            // 'Authorization' => $request->header('Authorization'), // Uncomment if needed
        ])->post($studentsApiUrl, [
            'student_ids' => $studentIds,
        ]);
    
        if (!$response->ok()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch student info from user management service.',
            ], $response->status());
        }
    
        // Assume the API returns an array keyed by student_id
        $studentInfoMap = collect($response->json())->keyBy('slug');
    
        // Merge student info with enrollments
        $students = $enrollments->map(function ($enrollment) use ($studentInfoMap) {
            return [
                'enrollment' => $enrollment,
                'student_info' => $studentInfoMap->get($enrollment->student_id),
            ];
        });
    
        return response()->json([
            'success' => true,
            'data' => $students,
        ]);
    }

    public function store (Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'string'],
            'academic_class_section_id' => ['required', 'exists:academic_class_sections,id'],
            'roll_number' => ['nullable', 'integer'],
            'admission_date' => ['nullable', 'date'],
            'enrollment_type' => ['required', Rule::in(['new', 'transfer', 're-admission'])],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'graduation_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'graduated', 'transferred', 'dropped'])],
            'remarks' => ['nullable', 'string'],
        ]);
    
        // Load class and academic year with a single query using eager loading
        $section = AcademicClassSection::with(['academicYear', 'class'])
            ->findOrFail($validated['academic_class_section_id']);
    
        // Check for existing enrollment for the same student, class, and year
        $alreadyEnrolled = StudentEnrollment::where('student_id', $validated['student_id'])
            ->whereHas('academicClassSection', function ($query) use ($section) {
                $query->where('academic_year_id', $section->academic_year_id);
            })
            ->exists();
    
        if ($alreadyEnrolled) {
            return response()->json([
                'message' => 'This student is already enrolled in the same class for the selected academic year.'
            ], 422);
        }
    
        // Create enrollment
        $enrollment = StudentEnrollment::create([
            'student_id' => $validated['student_id'],
            'academic_class_section_id' => $validated['academic_class_section_id'],
            'roll_number' => $request->input('roll_number'),
            'admission_date' => $request->input('admission_date'),
            'enrollment_type' => $request->input('enrollment_type', 'new'),
            'previous_school' => $request->input('previous_school'),
            'graduation_date' => $request->input('graduation_date'),
            'status' => $request->input('status', 'active'),
            'remarks' => $request->input('remarks'),
        ]);
    
        return response()->json([
            'message' => 'Enrollment successful.',
            'data' => $enrollment
        ], 201);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'exists:student_enrollments,slug'],
            'student_id' => ['required', 'string'],
            'academic_class_section_id' => ['required', 'exists:academic_class_sections,id'],
            'roll_number' => ['nullable', 'integer'],
            'admission_date' => ['nullable', 'date'],
            'enrollment_type' => ['required', Rule::in(['new', 'transfer', 're-admission'])],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'graduation_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'graduated', 'transferred', 'dropped'])],
            'remarks' => ['nullable', 'string'],
        ]);

        $enrollment = StudentEnrollment::where('slug',$validated['slug'])->firstOrFail();

        $section = AcademicClassSection::with(['academicYear', 'class'])
            ->findOrFail($validated['academic_class_section_id']);

        // Check for duplicate enrollment (excluding the current one)
        $alreadyEnrolled = StudentEnrollment::where('id', '!=', $enrollment->id)
            ->where('student_id', $validated['student_id'])
            ->whereHas('academicClassSection', function ($query) use ($section) {
                $query->where('academic_year_id', $section->academic_year_id);
            })
            ->exists();

        if ($alreadyEnrolled) {
            return response()->json([
                'message' => 'This student is already enrolled in the same class for the selected academic year.'
            ], 422);
        }

        // Update fields
        $enrollment->update([
            'student_id' => $validated['student_id'],
            'academic_class_section_id' => $validated['academic_class_section_id'],
            'roll_number' => $request->input('roll_number'),
            'admission_date' => $request->input('admission_date'),
            'enrollment_type' => $request->input('enrollment_type', 'new'),
            'previous_school' => $request->input('previous_school'),
            'graduation_date' => $request->input('graduation_date'),
            'status' => $request->input('status', 'active'),
            'remarks' => $request->input('remarks'),
        ]);

        return response()->json([
            'message' => 'Enrollment updated successfully.',
            'data' => $enrollment
        ]);
    }

    public function handleAction(Request $request)
    {
        $request->validate([
            'slug' => 'required|string|exists:student_enrollments,slug',
            'action' => 'required|string|in:active,graduated,transferred,dropped',
        ]);

        $slug = $request->input('slug');
        $action = $request->input('action');

        // Fetch soft-deleted teachers as well
        $enrollment = StudentEnrollment::withTrashed()->where('slug', $slug)->firstOrFail();

        switch ($action) {
            case 'active':
                $enrollment->status = 'active';
                $enrollment->save();
                return response()->json(['message' => 'Enrollment status set to active']);

            case 'graduated':
                $enrollment->status = 'graduated';
                $enrollment->save();
                return response()->json(['message' => 'Enrollment graduated']);

            case 'transferred':
                $enrollment->status = 'transferred';
                $enrollment->save();
                return response()->json(['message' => 'Enrollment transferred']);

            case 'dropped':
                $enrollment->status = 'dropped';
                $enrollment->save();
                return response()->json(['message' => 'Enrollment dropped']);

            default:
                return response()->json(['message' => 'Invalid action'], 400);
        }
    }

    
}
