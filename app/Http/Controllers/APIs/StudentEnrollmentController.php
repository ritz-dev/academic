<?php

namespace App\Http\Controllers\APIs;

use App\Models\StudentLeave;
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
        try {
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

            $total = (clone $query)->count();

            if (!empty($validated['skip'])) {
                $query->skip($validated['skip']);
            }
            if (!empty($validated['limit'])) {
                $query->take($validated['limit']);
            }

            $enrollments = $query->get();

            // Extract unique student slugs
            $slugs = $enrollments->pluck('student_slug')->filter()->unique()->values();

            $studentData = collect();

            if ($slugs->isNotEmpty()) {
                $baseUrl = config('services.user_management.url');
                $endpoint = "{$baseUrl}students";

                if ($endpoint) {
                    $response = Http::withHeaders([
                        'Accept' => 'application/json',
                        // You can include auth header if needed
                        // 'Authorization' => $request->header('Authorization'),
                    ])->post($endpoint, [
                        'slugs' => $slugs
                    ]);

                    if ($response->successful()) {
                        $studentData = collect($response->json('data'))->keyBy('slug');
                    }
                }
            }

            // Merge student data into enrollment records
            $enriched = $enrollments->map(function ($enrollment) use ($studentData) {
                return array_merge($enrollment->toArray(), [
                    'student' => $studentData[$enrollment->student_slug] ?? null,
                ]);
            });

            return response()->json([
                'status' => 'OK! The request was successful',
                'total' => $total,
                'data' => $enriched,
            ]);
        } catch (\Exception $e) {   
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store (Request $request)
    {
        try {
            $validated = $request->validate([
                'student_slug' => ['required', 'string'],
                'academic_class_section_slug' => ['required', 'exists:academic_class_sections,slug'],
                'roll_number' => ['nullable', 'integer'],
                'admission_date' => ['nullable', 'date'],
                'enrollment_type' => ['required', Rule::in(['new', 'transfer', 're-admission'])],
                'previous_school' => ['nullable', 'string', 'max:255'],
                'graduation_date' => ['nullable', 'date'],
                'academic_info' => ['nullable', 'string'],
                'status' => ['required', Rule::in(['active', 'graduated', 'transferred'])],
                'remarks' => ['nullable', 'string'],
            ]);

            $baseUrl = config('services.user_management.url');
            $endpoint = "{$baseUrl}students/show";

            if ($endpoint) {
                $studentResponse = Http::withHeaders([
                    'Accept' => 'application/json',
                    // You can include auth header if needed
                    // 'Authorization' => $request->header('Authorization'),
                ])->post($endpoint, [
                    'slug' => $validated['student_slug']
                ]);
            }

            
            if (!$studentResponse->ok()) {
                // Remove `$this->command->error(...)` — this is used in Artisan CLI, not in HTTP controllers
                return response()->json([
                    'message' => 'Failed to fetch student from user management service.',
                ], 500);
            }
        
            // Load class and academic year with a single query using eager loading
            $section = AcademicClassSection::with(['academicYear'])->where('slug', $validated['academic_class_section_slug'])->firstOrFail();
        
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
                'student_name' => $studentResponse->json()['student_name'],
                'roll_number' => $request->input('roll_number'),
                'admission_date' => $request->input('admission_date'),
                'enrollment_type' => $request->input('enrollment_type', 'new'),
                'previous_school' => $request->input('previous_school'),
                'graduation_date' => $request->input('graduation_date'),
                'academic_info' => $request->input('academic_info'),
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

    public function show (Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => ['required', 'string', 'exists:student_enrollments,slug'],
            ]);

            $enrollment = StudentEnrollment::where('slug', $validated['slug'])
                ->with(['academicClassSection.academicYear'])
                ->firstOrFail();

            if (!$enrollment) {
                return response()->json([
                    'status' => 'Not Found',
                    'message' => 'Enrollment not found'
                ], 404);
            }

            $baseUrl = config('services.user_management.url');
            $endpoint = "{$baseUrl}students/show";

            $enrollmentData = null;

            if ($endpoint) {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    // You can include auth header if needed
                    // 'Authorization' => $request->header('Authorization'),
                ])->post($endpoint, [
                    'slug' => $enrollment->student_slug
                ]);
            }

            if ($response->successful()) {
                $fetched = $response->json();
                $enrollmentData = $fetched;
            }

            $enrollment->student = $enrollmentData;

            return response()->json($enrollment);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update (Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => ['required', 'string', 'exists:student_enrollments,slug'],
                'student_slug' => ['required', 'string'],
                'academic_class_section_slug' => ['required', 'exists:academic_class_sections,slug'],
                'roll_number' => ['nullable', 'integer'],
                'admission_date' => ['nullable', 'date'],
                'enrollment_type' => ['required', Rule::in(['new', 'transfer', 're-admission'])],
                'previous_school' => ['nullable', 'string', 'max:255'],
                'graduation_date' => ['nullable', 'date'],
                'status' => ['required', Rule::in(['active', 'graduated', 'transferred'])],
                'acaemidc_info' => ['nullable', 'string'],
                'remarks' => ['nullable', 'string'],
            ]);
    
            $enrollment = StudentEnrollment::where('slug',$validated['slug'])->firstOrFail();

            $baseUrl = config('services.user_management.url');
            $endpoint = "{$baseUrl}students/show";

            if ($endpoint) {
                $studentResponse = Http::withHeaders([
                    'Accept' => 'application/json',
                    // You can include auth header if needed
                    // 'Authorization' => $request->header('Authorization'),
                ])->post($endpoint, [
                    'slug' => $validated['student_slug']
                ]);
            }

            if (!$studentResponse->ok()) {
                // Remove `$this->command->error(...)` — this is used in Artisan CLI, not in HTTP controllers
                return response()->json([
                    'message' => 'Failed to fetch student from user management service.',
                ], 500);
            }

            $section = AcademicClassSection::with(['academicYear'])->where('slug', $validated['academic_class_section_slug'])->firstOrFail();
    
            // Check for duplicate enrollment (excluding the current one)
            $alreadyEnrolled = StudentEnrollment::where('slug', '!=', $enrollment->slug)
                ->where('student_slug', $validated['student_slug'])
                ->whereHas('academicClassSection', function ($query) use ($section) {
                    $query->where('academic_year_slug', $section->academic_year_slug);
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
                'student_name' => $studentResponse->json()['student_name'],
                'roll_number' => $request->input('roll_number'),
                'admission_date' => $request->input('admission_date'),
                'enrollment_type' => $request->input('enrollment_type', 'new'),
                'previous_school' => $request->input('previous_school'),
                'graduation_date' => $request->input('graduation_date'),
                'status' => $request->input('status', 'active'),
                'academic_info' => $request->input('academic_info'),
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

    public function handleAction (Request $request)
    {
        $request->validate([
            'slug' => 'required|string|exists:student_enrollments,slug',
            'action' => 'required|string|in:active,graduated,transferred,dropped,restore,delete',
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
                $student->delete();
                return response()->json(['message' => 'Enrollment dropped']);

            case 'delete':
                $enrollment->forceDelete();
                return response()->json(['message' => 'Enrollment permanently deleted']);

            case 'restore':
                if ($student->trashed()) {
                    $student->restore();
                    $student->status = 'active';
                    $student->save();
                    return response()->json(['message' => 'Student restored']);
                }
                return response()->json(['message' => 'Student is not deleted'], 400);    
                
            default:
                return response()->json(['message' => 'Invalid action'], 400);
        }
    }

    public function byClassSection (Request $request)
    {
        try {
            $validated = $request->validate([
                'academic_class_section_slug' => ['required', 'string', 'exists:academic_class_sections,slug'],
                'weekly_schedule_slug' => ['required', 'string'],
            ]);

            $enrollments = StudentEnrollment::where('academic_class_section_slug', $validated['academic_class_section_slug'])->get();

            if ($enrollments->isEmpty()) {
                return response()->json([
                    'status' => 'No enrollments found for this class section',
                    'data' => []
                ], 404);
            }

            //Check student attendance for the given weekly schedule and today
            $today = now()->format('Y-m-d');

            $attendance = StudentLeave::where('weekly_schedule_slug', $validated['weekly_schedule_slug'])
                ->whereDate('date', $today)
                ->where('academic_class_section_slug', $validated['academic_class_section_slug'])
                ->whereIn('student_enrollment_slug', $enrollments->pluck('slug'))
                ->get();

            logger($attendance);

            // Attach attendance status to each enrollment
            foreach ($enrollments as $enrollment) {
                $enrollment->leave_status = optional(
                    $attendance->where('student_enrollment_slug', $enrollment->slug)->first()
                )->status;
                $enrollment->leave_type = optional(
                    $attendance->where('student_enrollment_slug', $enrollment->slug)->first()
                )->leave_type;
            }

            return response()->json([
                'status' => 'OK! The request was successful',
                'data' => $enrollments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
