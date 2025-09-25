<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function index()
    {
        try {
            $validated = $request->validate([
                'teacher_slug' => ['nullable', 'string', 'exists:teachers,slug'],
                'academic_class_section_slug' => ['nullable', 'string', 'exists:academic_class_sections,slug'],
                'subject_slug' => ['nullable', 'string', 'exists:subjects,slug'],
                'type' => ['nullable', 'in:quiz,test,exam,assignment'],
                'is_published' => ['nullable', 'boolean'],
                'date' => ['nullable', 'date'],
                'start_date' => ['nullable', 'date'],
                'end_date' => ['nullable', 'date'],
                'limit' => ['nullable', 'integer', 'min:1'],
                'skip' => ['nullable', 'integer', 'min:0'],
            ]);

            $query = Assessment::query()
                ->when(!empty($validated['teacher_slug']), fn($q) =>
                    $q->where('teacher_slug', $validated['teacher_slug']))
                ->when(!empty($validated['academic_class_section_slug']), fn($q) =>
                    $q->where('academic_class_section_slug', $validated['academic_class_section_slug']))
                ->when(!empty($validated['subject_slug']), fn($q) =>
                    $q->where('subject_slug', $validated['subject_slug']))
                ->when(!empty($validated['type']), fn($q) =>
                    $q->where('type', $validated['type']))
                ->when(isset($validated['is_published']), fn($q) =>
                    $q->where('is_published', $validated['is_published']))
                ->when(!empty($validated['date']), fn($q) =>
                    $q->whereDate('date', $validated['date']))
                ->when(!empty($validated['start_date']) && !empty($validated['end_date']), function ($q) use ($validated) {
                    $q->whereBetween('date', [
                        Carbon::parse($validated['start_date'])->toDateString(),
                        Carbon::parse($validated['end_date'])->toDateString()
                    ]);
                })
                ->when(!empty($validated['start_date']) && empty($validated['end_date']), function ($q) use ($validated) {
                    $q->where('date', '>=', Carbon::parse($validated['start_date'])->toDateString());
                })
                ->when(!empty($validated['end_date']) && empty($validated['start_date']), function ($q) use ($validated) {
                    $q->where('date', '<=', Carbon::parse($validated['end_date'])->toDateString());
                })
                ->orderByDesc('date');

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
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string',
                'teacher_slug' => 'required|string',
                'academic_class_section_slug' => 'required|exists:academic_class_sections,slug',
                'subject_slug' => 'required|exists:subjects,slug',
                'type' => 'required|in:quiz,test,exam,assignment',
                'date' => 'required|integer',
                'due_date' => 'nullable|date',
                'max_marks' => 'required|integer',
                'min_marks' => 'required|integer',
                'description' => 'nullable|string',
                'is_published' => 'boolean',
            ]);

            $validated['date'] = (int) \Carbon\Carbon::parse($validated['date'])->format('Ymd');
            $validated['due_date'] = (int) \Carbon\Carbon::parse($validated['due_date'])->format('Ymd');
            
            $assessment = Assessment::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Assessment created successfully.',
                'data' => $assessment,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create assessment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $assessment = Assessment::findOrFail($id);
        $assessment->update($request->all());
        return $assessment;
    }

    public function destroy($id)
    {
        Assessment::destroy($id);
        return response()->noContent();
    }
}
