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
        try {
            $validated = $request->validate([
                'year' => 'nullable|string',
                'academic_year_slug' => 'nullable|string|exists:academic_years,slug',
                'academic_class_slug' => 'nullable|string|exists:academic_classes,slug',
                'limit' => 'nullable|integer|min:1',
                'skip' => 'nullable|integer|min:0',
            ]);
        
            $query = AcademicClassSection::with(['academicYear', 'academicClass', 'academicSection'])
                ->when(!empty($validated['academic_year_slug']), fn($q) => $q->where('academic_year', $validated['academic_year_slug']))
                ->when(!empty($validated['academic_class_slug']), fn($q) => $q->where('class', $validated['academic_class_slug']))
                ->when(!empty($validated['year']), fn($q) => $q->where('year', 'like', '%' . $validated['year'] . '%'));
        
            $total = (clone $query)->count();
        
            // Apply skip and limit
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
                'data' => $results->map(fn($item) => $this->transform($item)),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Bad Request! The request is invalid.',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    private function transform($item)
    {
        return [
            'slug' => $item->slug,
            'academicYear' => new AcademicYearResource($item->academicYear),
            'academicClass' => new AcademicClassResource($item->academicClass),
            'academicSection' => new SectionResource($item->academicSection),
        ];
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'academic_year_slug' => 'required|string|exists:academic_years,slug',
                'academic_class_slug' => 'required|string|exists:academic_classes,slug',
                'academic_section_slug' => 'required|string|exists:sections,slug',
            ]);

            $trashedRecord = AcademicClassSection::onlyTrashed()
            ->where('academic_year_slug', $validated['academic_year_slug'])
            ->where('class_slug', $validated['academic_class_slug'])
            ->where('section_slug', $validated['academic_section_slug'])
            ->first();

            if ($trashedRecord) {
                // Restore the trashed record
                $trashedRecord->restore();

                return response()->json([
                    'status' => 'Created successfully (restored from trash)',
                    'data' => $this->transform($trashedRecord->load(['academicYear', 'academicClass', 'academicSection'])),
                ]);
            }

            $academicClassSection = AcademicClassSection::create([
                'academic_year_slug' => $validated['academic_year_slug'],
                'class_slug' => $validated['academic_class_slug'],
                'section_slug' => $validated['academic_section_slug'],
            ]);

            return response()->json([
                'status' => 'Created successfully',
                'data' => $this->transform($academicClassSection->load(['academicYear', 'academicClass', 'academicSection'])),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'An error occurred while creating the academic class section.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:academic_class_sections,slug',
                'academic_year_slug' => 'required|string|exists:academic_years,slug',
                'academic_class_slug' => 'required|string|exists:academic_classes,slug',
                'academic_section_slug' => 'required|string|exists:sections,slug'
            ]);

            $academicClassSection = AcademicClassSection::where('slug', $validated['slug'])->firstOrFail();

            $academicClassSection->update([
                'academic_year_slug' => $validated['academic_year_slug'],
                'class_slug' => $validated['academic_class_slug'],
                'section_slug' => $validated['academic_section_slug'],

            ]);

            return response()->json([
                'status' => 'Updated successfully',
                'data' => $this->transform($academicClassSection->load(['academicYear', 'academicClass', 'academicSection'])),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'An error occurred while updating the academic class section.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string',
            ]);

            $academicClassSection = AcademicClassSection::where('slug', $validated['slug'])->firstOrFail();

            $academicClassSection->delete();

            return response()->json([
                'status' => 'Deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'An error occurred while deleting the academic class section.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
