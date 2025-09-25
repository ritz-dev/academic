<?php

namespace App\Http\Controllers\APIs;

use Exception;
use App\Models\Holiday;
use App\Models\TimeTable;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\HolidayResource;
use App\Http\Resources\TimeTableResource;
use Illuminate\Validation\ValidationException;

class HolidayController extends Controller
{

    public function list(Request $request)
    {
        try {
            $limit = (int) $request->limit;
            $search = $request->search;

            $query = Holiday::orderBy('id', 'desc');

            if ($search) {
                $query->where('name', 'LIKE', $search . '%');
            }

            $data = $limit ? $query->paginate($limit) : $query->get();

            $data = HolidayResource::collection($data);

            $total = Holiday::count();

            return response()->json([
                "status" => "OK! The request was successful",
                "total" => $total,
                "data" => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'Bad Request!. The request is invalid.',
                'message' => $e->getMessage()
            ],400);
        }
    }

    public function create(Request $request){
        try{
            $request->validate([
                "name" => "required"
            ]);

            $holiday = new Holiday;
            $holiday->slug = Str::uuid();
            $holiday->name = $request->name;
            $holiday->save();

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
                'slug' => 'required|exists:holidays,slug'
            ]);

            $data = Holiday::where('slug',$request->slug)->firstOrFail();
            $holiday = new HolidayResource($data);
            return response()->json($holiday,200);

        }catch (Exception $e) {
            return response()->json([
                'status' => 'An error occurred while showing.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try{
            $request->validate([
                'name' => 'required'
            ]);

            $holiday = Holiday::where('slug',$request->slug)->firstOrFail();
            $holiday->name = $request->name;
            $holiday->save();

            $holiday = new HolidayResource($holiday);

            return response()->json($holiday, 200);
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
            $holiday = Holiday::where('slug',$request->slug)->firstOrFail();
            $holiday->delete();

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
