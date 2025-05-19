<?php

namespace App\Http\Controllers\APIs;

use Illuminate\Http\Request;
use App\Models\StudentEnrollment;
use App\Http\Controllers\Controller;
use App\Models\AcademicClassSection;

class StudentEnrollmentController extends Controller
{
    public function byAcademicYear(Request $request)
    {
        // Validate the request if needed
        $request->validate([
            'slug'  => 'required'
        ]);

        $section = AcademicClassSection::where('slug', $request->slug)->firstOrFail();

        $students = StudentEnrollment::where('academic_class_section_id', $section->id)->get();

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }
}
