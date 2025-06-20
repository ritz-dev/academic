<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;

class AssessmentResultController extends Controller
{
    public function index()
    {
        return AssessmentResult::all();
    }

    public function show($id)
    {
        return AssessmentResult::findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|unique:assessment_results',
            'assessment_id' => 'required|exists:assessments,id',
            'student_id' => 'required',
            'marks_obtained' => 'nullable|integer',
            'remarks' => 'nullable|string',
            'status' => 'required|in:pending,graded,reviewed',
            'graded_by' => 'nullable|string',
            'graded_at' => 'nullable|date',
        ]);
        return AssessmentResult::create($validated);
    }

    public function update(Request $request, $id)
    {
        $result = AssessmentResult::findOrFail($id);
        $result->update($request->all());
        return $result;
    }

    public function destroy($id)
    {
        AssessmentResult::destroy($id);
        return response()->noContent();
    }
}
