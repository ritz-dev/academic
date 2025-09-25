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
                'data' => $results->map(function ($item) {
                    return [
                        'slug'          => $item->slug ?? '',
                        'year_slug'     => $item->academicYear?->slug ?? '',
                        'class_slug'    => $item->academicClass?->slug ?? '',
                        'section_slug'  => $item->academicSection?->slug ?? '',
                        'year_name'     => $item->academicYear?->year ?? '',
                        'class_name'    => $item->academicClass?->name ?? '',
                        'section_name'  => $item->academicSection?->name ?? '',
                    ];
                }),
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
                'year_slug' => 'required|string|exists:academic_years,slug',
                'class_slug' => 'required|string|exists:academic_classes,slug',
                'section_slug' => 'required|string|exists:sections,slug',
            ]);

            $existing = AcademicClassSection::where('academic_year_slug', $validated['year_slug'])
                ->where('class_slug', $validated['class_slug'])
                ->where('section_slug', $validated['section_slug'])
                ->first();

            if ($existing) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Academic class section already exists.',
                ], 409); // 409 Conflict
            }

            $trashedRecord = AcademicClassSection::onlyTrashed()
            ->where('academic_year_slug', $validated['year_slug'])
            ->where('class_slug', $validated['class_slug'])
            ->where('section_slug', $validated['section_slug'])
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
                'academic_year_slug' => $validated['year_slug'],
                'class_slug' => $validated['class_slug'],
                'section_slug' => $validated['section_slug'],
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

    public function update(Request $request,)
    {
        try {
            $validated = $request->validate([
                'slug' => 'required|string|exists:academic_class_sections,slug',
                'year_slug' => 'required|string|exists:academic_years,slug',
                'class_slug' => 'required|string|exists:academic_classes,slug',
                'section_slug' => 'required|string|exists:sections,slug',
            ]);

            $existing = AcademicClassSection::where('slug', $validated['slug'])->first();

            if (!$existing) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Academic class section not found.',
                ], 404);
            }

            // Check if another record with the same combination exists (excluding this one)
            $duplicate = AcademicClassSection::withTrashed()
                ->where('academic_year_slug', $validated['year_slug'])
                ->where('class_slug', $validated['class_slug'])
                ->where('section_slug', $validated['section_slug'])
                ->where('slug', '!=', $validated['slug'])
                ->first();

            if ($duplicate) {
                if ($duplicate->trashed()) {
                    return response()->json([
                        'status' => 'fail',
                        'message' => 'Another academic class section with the same values exists in trash.',
                    ], 409);
                }

                return response()->json([
                    'status' => 'fail',
                    'message' => 'Academic class section already exists.',
                ], 409);
            }

            // Perform update
            $existing->update([
                'academic_year_slug' => $validated['year_slug'],
                'class_slug' => $validated['class_slug'],
                'section_slug' => $validated['section_slug'],
            ]);

            return response()->json([
                'status' => 'Updated successfully',
                'data' => $this->transform($existing->fresh()->load(['academicYear', 'academicClass', 'academicSection'])),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        }  catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
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
