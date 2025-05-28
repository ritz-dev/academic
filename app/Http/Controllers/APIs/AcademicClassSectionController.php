<?php

namespace App\Http\Controllers\APIs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AcademicClassSection;
use App\Http\Resources\SectionResource;
use App\Http\Resources\AcademicYearResource;
use App\Http\Resources\AcademicClassResource;

class AcademicClassSectionController extends Controller
{
    public function index(Request $request)  
    {
        $validated = $request->validate([
            'academic_year_slug' => 'nullable|string|exists:academic_years,slug',
            'academic_class_slug' => 'nullable|string|exists:academic_classes,slug',
        ]);
    
        $query = AcademicClassSection::with(['academicYear', 'academicClass', 'academicSection']);
    
        
        $query->where(function ($q) use ($validated) {
            if (!empty($validated['academic_year_slug'])) {
                $q->where('academic_year', $validated['academic_year_slug']);

                if (!empty($validated['academic_class_slug'])) {
                    $q->where('class', $validated['academic_class_slug']);
                }
            }    
        });

        $academicClassSections = $query->get();

        if ($academicClassSections->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No academic class sections found for the given criteria.',
                'data' => []
            ], 404);
        }
    
        return response()->json([
            'success' => true,
            'data' => $academicClassSections->map(function ($item) {
                return [
                    'slug' => $item->slug,
                    'academicYear' => new AcademicYearResource($item->academicYear),
                    'academicClass' => new AcademicClassResource($item->academicClass),
                    'academicSection' => new SectionResource($item->academicSection),
                ];
            }),
        ]);
    }
}
