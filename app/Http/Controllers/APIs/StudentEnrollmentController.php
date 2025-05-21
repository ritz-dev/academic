<?php

namespace App\Http\Controllers\APIs;

use Illuminate\Http\Request;
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

    public function create (Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|string|max:255',
            'academic_class_section_id' => 'required|exists:academic_class_sections,id',
            // other fields...
        ]);
    
        // Load class and academic year with a single query using eager loading
        $section = AcademicClassSection::with(['academicYear', 'class'])
            ->findOrFail($validated['academic_class_section_id']);
    
        // Check for existing enrollment for the same student, class, and year
        $alreadyEnrolled = StudentEnrollment::where('student_id', $validated['student_id'])
            ->whereHas('academicClassSection', function ($query) use ($section) {
                $query->where('class_id', $section->class_id)
                      ->where('academic_year_id', $section->academic_year_id);
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
    
        // Extract all section IDs
        // $sectionIds = $enrollments->pluck('academic_class_section_id')->toArray();
    
        // // Send POST request to fetch section details
        // $sectionsApiUrl = config('services.user_management.url') . 'students/enrollment';
    
        // $response = Http::withHeaders([
        //     'Accept' => 'application/json',
        //     // 'Authorization' => $request->header('Authorization'), // Uncomment if needed
        // ])->post($sectionsApiUrl, [
        //     'section_ids' => $sectionIds,
        // ]);
    
        // if (!$response->ok()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Failed to fetch section info from user management service.',
        //     ], $response->status());
        // }
    
        // // Assume the API returns an array keyed by section_id
        // $sectionInfoMap = collect($response->json())->keyBy('id');
    
        // // Merge section info with enrollments
        // $sections = $enrollments->map(function ($enrollment) use ($sectionInfoMap) {
        //     return [
        //         'enrollment' => $enrollment,
        //         'section_info' => $sectionInfoMap->get($enrollment->academic_class_section_id),
        //     ];
        // });
    
        return response()->json([
            'success' => true,
            'data' => $enrollments,
        ]);
    }
}
