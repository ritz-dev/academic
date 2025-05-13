<?php

namespace App\Http\Controllers\APIs;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\AcademicYearResource;
use Illuminate\Validation\ValidationException;


class AcademicYearController extends Controller
{

    public function index(Request $request)
    {
        return response()->json(AcademicYear::get());
        // try {
        //     $limit = (int) $request->limit;
        //     $search = $request->search;

        //     $query = AcademicYear::orderBy('id', 'desc');

        //     if ($search) {
        //         $query->where('year', 'LIKE', $search . '%');
        //     }

        //     $data = $limit ? $query->paginate($limit) : $query->get();

        //     $data = AcademicYearResource::collection($data);

        //     $total = AcademicYear::count();

        //     return response()->json([
        //         "status" => "OK! The request was successful",
        //         "total" => $total,
        //         "data" => $data
        //     ], 200);
        // } catch (Exception $e) {
        //     return response()->json([
        //         'status' => 'Bad Request!. The request is invalid.',
        //         'message' => $e->getMessage()
        //     ],400);
        // }
    }

    public function create(Request $request)
    {
        try {
            $request->validate([
                'year' => 'required|string|unique:academic_years',
                'startDate' => 'required|date',
                'endDate' => 'required|date|after:startDate',
                'status' => 'required|string|in:Upcoming,In Progress,Completed'
            ]);

            $start_date = Carbon::parse($request->startDate)->format('Y-m-d');
            $end_date = Carbon::parse($request->endDate)->format('Y-m-d');

            $academic_year = new AcademicYear;
            $academic_year->slug = Str::uuid();
            $academic_year->year = $request->year;
            $academic_year->start_date = $start_date;
            $academic_year->end_date = $end_date;
            $academic_year->status = $request->status;
            $academic_year->save();

            return response()->json([
                "status" => "OK! The request was successful",
            ],200);

        }catch (ValidationException $e) {
            return response()->json([
                'status' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while adding.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function detail(Request $request)
    {
        try {
            $request->validate([
                'slug' => 'required|exists:academic_years,slug'
            ]);

            $data = AcademicYear::where('slug',$request->slug)->firstOrFail();
            $academic_year = new AcademicYearResource($data);
            return response()->json($academic_year);
        }catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while showing.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $academic_year = AcademicYear::where('slug',$request->slug)->firstOrFail();

           $request->validate([
                'year' => [
                    'required',
                    Rule::unique('academic_years')->ignore($request->slug, 'slug'),
                ],
                'startDate' => 'required|date',
                'endDate' => 'required|date|after:startDate',
                'status' => 'required|string|in:Upcoming,In Progress,Completed'
            ]);

            $start_date = Carbon::parse($request->startDate)->format('Y-m-d');
            $end_date = Carbon::parse($request->endDate)->format('Y-m-d');


            $academic_year->year = $request->year;
            $academic_year->start_date = $start_date;
            $academic_year->end_date = $end_date;
            $academic_year->status = $request->status;
            $academic_year->save();

            $academic_year = new AcademicYearResource($academic_year);

            return response()->json($academic_year,200);
        }catch (ValidationException $e) {
            return response()->json([
                'status' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while editing.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $academicYear = AcademicYear::where('slug',$request->slug)->firstOrFail();
            $academicYear->delete();

            return response()->json([
                "status" => "OK! Deleting Successfully."
            ],200);
        }catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while deleting.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
