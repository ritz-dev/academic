<?php

namespace App\Http\Controllers\APIs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AcademicClassSection;
use Illuminate\Support\Facades\Http;

class AcademicClassSectionController extends Controller
{
    public function index(Request $request)  
    {
        // return response()->json(AcademicClassSection::with('academicYear','class','section','subjects')->get());
        $userManagementServiceUrl = config('services.user_management.url') . '/students/by-section';

        $teacherApiUrl = config('services.user_management.url') . 'teachers';

            // Fetch teacher info based on the section ID
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => $request->header('Authorization'),
            ])->post($teacherApiUrl, []);

            // Check if the response is successful
            if ($response->failed()) {
                return response()->json(['error' => 'Unable to fetch teacher info'], 400);
            }

            // Parse the response data
            return $response->json();
    }
}
