<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIs\ExamController;
use App\Http\Controllers\APIs\GradeController;
use App\Http\Controllers\APIs\HolidayController;
use App\Http\Controllers\APIs\SectionController;
use App\Http\Controllers\APIs\SubjectController;
use App\Http\Controllers\APIs\CertificateController;
use App\Http\Controllers\APIs\AcademicYearController;
use App\Http\Controllers\APIs\ExamScheduleController;
use App\Http\Controllers\APIs\StudentLeaveController;
use App\Http\Controllers\APIs\AcademicClassController;
use App\Http\Controllers\APIs\DailyScheduleController;
use App\Http\Controllers\APIs\WeeklyScheduleController;
use App\Http\Controllers\APIs\StudentEnrollmentController;
use App\Http\Controllers\APIs\AcademicAttendanceController;
use App\Http\Controllers\APIs\AcademicClassSectionController;

Route::prefix('class-sections')->group(function(){
    Route::post('/',[AcademicClassSectionController::class,'index']);
    Route::post('store',[AcademicClassSectionController::class,'store']);
    Route::post('show',[AcademicClassSectionController::class,'show']);
    Route::post('update',[AcademicClassSectionController::class,'update']);
    Route::post('delete',[AcademicClassSectionController::class,'delete']);
});

Route::prefix('academic-years')->group(function(){
    Route::post('/',[AcademicYearController::class,'index']);
    Route::post('store',[AcademicYearController::class,'store']);
    Route::post('show',[AcademicYearController::class,'show']);
    Route::post('update',[AcademicYearController::class,'update']);
    Route::post('action',[AcademicYearController::class,'handleAction']);
});

Route::prefix('classes')->group(function(){
    Route::post('/',[AcademicClassController::class,'index']);
    Route::post('store',[AcademicClassController::class,'store']);
    Route::post('show',[AcademicClassController::class,'show']);
    Route::post('update',[AcademicClassController::class,'update']);
    Route::post('action',[AcademicClassController::class,'handleAction']);
});

Route::prefix('sections')->group(function(){
    Route::post('/',[SectionController::class,'index']);
    Route::post('store',[SectionController::class,'store']);
    Route::post('show',[SectionController::class,'show']);
    Route::post('update',[SectionController::class,'update']);
    Route::post('action',[SectionController::class,'handleAction']);
});

Route::prefix('grades')->group(function(){
    Route::post('list',[GradeController::class,'list']);
    Route::post('create',[GradeController::class,'create']);
    Route::post('detail',[GradeController::class,'detail']);
    Route::post('update',[GradeController::class,'update']);
    Route::post('delete',[GradeController::class,'delete']);
});

Route::prefix('subjects')->group(function(){
    Route::post('/',[SubjectController::class,'index']);
    Route::post('store',[SubjectController::class,'store']);
    Route::post('show',[SubjectController::class,'show']);
    Route::post('update',[GradeController::class,'update']);
    Route::post('action',[SubjectController::class,'handleAction']);
});

Route::prefix('weekly-schedule')->group(function () {
    Route::post('/',[WeeklyScheduleController::class,'index']);
    Route::post('section',[WeeklyScheduleController::class,'bySection']);
    Route::post('store', [WeeklyScheduleController::class, 'store']);
    Route::post('update',[WeeklyScheduleController::class,'update']);
    Route::post('delete',[WeeklyScheduleController::class,'delete']);
    Route::post('delete-by-section',[WeeklyScheduleController::class,'deleteBySection']);
});

Route::prefix('daily-schedule')->group(function () {
    Route::post('section',[DailyScheduleController::class,'bySection']);
    Route::post('by-teacher',[DailyScheduleController::class,'byTeacherAcademicYear']);
    Route::post('store', [DailyScheduleController::class, 'store']);
});

Route::prefix('student-enrollment')->group(function(){
    Route::post('/',[StudentEnrollmentController::class,'index']);
    Route::post('store',[StudentEnrollmentController::class,'store']);
    Route::post('show',[StudentEnrollmentController::class,'show']);
    Route::post('update',[StudentEnrollmentController::class,'update']);
    Route::post('action',[StudentEnrollmentController::class,'handleAction']);
    Route::post('by-class-section',[StudentEnrollmentController::class,'byClassSection']);
});

Route::prefix('attendance')->group(function(){
    Route::post('/',[AcademicAttendanceController::class,'index']);
    Route::post('store',[AcademicAttendanceController::class,'store']);
    Route::post('show',[AcademicAttendanceController::class,'show']);
    Route::post('update',[AcademicAttendanceController::class,'update']);
    Route::post('action',[AcademicAttendanceController::class,'handleAction']);
    Route::post('delete',[AcademicAttendanceController::class,'delete']);
});

Route::prefix('student-leave')->group(function(){
    Route::post('/',[StudentLeaveController::class,'index']);
    Route::post('store',[StudentLeaveController::class,'store']);
    Route::post('show',[StudentLeaveController::class,'show']);
    Route::post('update',[StudentLeaveController::class,'update']);
    Route::post('delete',[StudentLeaveController::class,'delete']);
});

// Route::prefix('exams')->group(function(){
//     Route::post('list',[ExamController::class,'list']);
//     Route::post('create',[ExamController::class,'create']);
//     Route::post('detail',[ExamController::class,'detail']);
//     Route::post('update',[ExamController::class,'update']);
//     Route::post('delete',[ExamController::class,'delete']);
// });

// Route::prefix('exam-schedules')->group(function(){
//     Route::post('list',[ExamScheduleController::class,'list']);
//     Route::post('create',[ExamScheduleController::class,'create']);
//     Route::post('detail',[ExamScheduleController::class,'detail']);
//     Route::post('update',[ExamScheduleController::class,'update']);
//     Route::post('delete',[ExamScheduleController::class,'delete']);
// });

// Route::prefix('holidays')->group(function(){
//     Route::post('list',[HolidayController::class,'list']);
//     Route::post('create',[HolidayController::class,'create']);
//     Route::post('detail',[HolidayController::class,'detail']);
//     Route::post('update',[HolidayController::class,'update']);
//     Route::post('delete',[HolidayController::class,'delete']);
// });

// Route::prefix('certificates')->group(function(){
//     Route::post('add-certificate',[CertificateController::class,'addCertificate']);
// });

// Health check routes
Route::get('/ping', function () {
    return response()->json(['status' => 'pong'], 200);
});

Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        $dbStatus = 'connected';
    } catch (\Exception $e) {
        $dbStatus = 'disconnected';
    }

    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'service' => 'Academic Service',
        'version' => '1.0.0',
        'database' => $dbStatus,
        'environment' => app()->environment()
    ], 200);
});

Route::any('gateway/{endpoint}', function ($endpoint) {
    // Forward to /api/$endpoint
    $method = request()->method();
    $data = request()->all();
    $url = url('/api/' . $endpoint);
    
    $response = Http::timeout(30)->$method($url, $data);
    
    return response($response->body(), $response->status())
        ->header('Content-Type', $response->header('Content-Type'));
})->where('endpoint', '.*');