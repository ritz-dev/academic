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
    public function index (Request $request)
    {
        $validated = $request->validate([
            'academic_year_slug' => ['string', 'exists:academic_years,slug'],
            'academic_class_section_slug' => ['string', 'exists:academic_class_sections,slug'],
            'student_slug' => ['nullable', 'string'],
            'enrollment_type' => ['nullable', 'in:new,transfer,re-admission'],
            'status' => ['nullable', 'in:active,graduated,transferred,dropped'],
            'limit' => ['nullable', 'integer', 'min:1'],
            'skip' => ['nullable', 'integer', 'min:0'],
            
        ]);

        $query = StudentEnrollment::query()
                ->join('academic_class_sections', 'student_enrollments.academic_class_section_slug', '=', 'academic_class_sections.slug')
                ->when(!empty($validated['academic_year_slug']), fn($q) =>
                    $q->where('academic_class_sections.academic_year_slug', $validated['academic_year_slug']))
                ->when(!empty($validated['academic_class_section_slug']), fn($q) =>
                    $q->where('student_enrollments.academic_class_section_slug', $validated['academic_class_section_slug']))
                ->when(!empty($validated['student_slug']), fn($q) =>
                    $q->where('student_enrollments.student_slug', $validated['student_slug']))
                ->when(!empty($validated['enrollment_type']), fn($q) =>
                    $q->where('student_enrollments.enrollment_type', $validated['enrollment_type']))
                ->when(!empty($validated['status']), fn($q) =>
                    $q->where('student_enrollments.status', $validated['status']))
                ->select('student_enrollments.*');

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
    }

    public function store (Request $request)
    {
        try {
            $validated = $request->validate([
                'student_slug' => ['required', 'string'],
                'academic_class_section_slug' => ['required', 'exists:academic_class_sections,slug'],
                'student_name' => ['required', 'string', 'max:255'],
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
                ->findOrFail($validated['academic_class_section_slug']);
        
            // Check for existing enrollment for the same student, class, and year
            $alreadyEnrolled = StudentEnrollment::where('student_slug', $validated['student_slug'])
                ->whereHas('academicClassSection', function ($query) use ($section) {
                    $query->where('academic_year_slug', $section->academic_year_slug);
                })
                ->exists();
        
            if ($alreadyEnrolled) {
                return response()->json([
                    'message' => 'This student is already enrolled in the same class for the selected academic year.'
                ], 422);
            }
        
            // Create enrollment
            $enrollment = StudentEnrollment::create([
                'student_slug' => $validated['student_slug'],
                'academic_class_section_slug' => $validated['academic_class_section_slug'],
                'student_name' => $request->input('student_name'),
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
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
        
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => ['required', 'string', 'exists:student_enrollments,slug'],
                'student_slug' => ['required', 'string'],
                'academic_class_section_slug' => ['required', 'exists:academic_class_sections,slug'],
                'student_name' => ['required', 'string', 'max:255'],
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
                'student_slug' => $validated['student_slug'],
                'academic_class_section_slug' => $validated['academic_class_section_slug'],
                'student_name' => $request->input('student_name'),
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
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
        
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
