<?php

namespace App\Http\Controllers\APIs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AcademicClassSection;

class AcademicClassSectionController extends Controller
{
    public function index(Request $request)  
    {
        // return response()->json(AcademicClassSection::with('academicYear','class','section','subjects')->get());
        $academicClassSections = AcademicClassSection::with('academicYear', 'class', 'section')->get();
        return response()->json([
            'success' => true,
            'data' => $academicClassSections
        ]);
    }
}
