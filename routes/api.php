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
use App\Http\Controllers\APIs\AcademicClassController;
use App\Http\Controllers\APIs\DailyScheduleController;
use App\Http\Controllers\APIs\WeeklyScheduleController;
use App\Http\Controllers\APIs\StudentEnrollmentController;
use App\Http\Controllers\APIs\AcademicAttendanceController;
use App\Http\Controllers\APIs\AcademicClassSectionController;

Route::prefix('class-sections')->group(function(){
    Route::post('/',[AcademicClassSectionController::class,'index']);
    // Route::post('list-post',[AcademicYearController::class,'list']);
    // Route::post('create',[AcademicYearController::class,'create']);
    // Route::post('detail',[AcademicYearController::class,'detail']);
    // Route::put('update',[AcademicYearController::class,'update']);
    // Route::post('delete',[AcademicYearController::class,'delete']);
});

Route::prefix('academic-years')->group(function(){
    Route::post('/',[AcademicYearController::class,'index']);
    Route::post('store',[AcademicYearController::class,'store']);
    Route::post('show',[AcademicYearController::class,'show']);
    Route::put('update',[AcademicYearController::class,'update']);
    Route::post('action',[AcademicYearController::class,'handleAction']);
});

Route::prefix('classes')->group(function(){
    Route::post('/',[AcademicClassController::class,'index']);
    Route::post('store',[AcademicClassController::class,'store']);
    Route::post('show',[AcademicClassController::class,'show']);
    Route::post('action',[AcademicClassController::class,'handleAction']);
});

Route::prefix('sections')->group(function(){
    Route::post('/',[SectionController::class,'index']);
    Route::post('store',[SectionController::class,'store']);
    Route::post('show',[SectionController::class,'show']);
    Route::post('action',[SectionController::class,'handleAction']);
});

Route::prefix('grades')->group(function(){
    Route::post('list',[GradeController::class,'list']);
    Route::post('create',[GradeController::class,'create']);
    Route::post('detail',[GradeController::class,'detail']);
    Route::put('update',[GradeController::class,'update']);
    Route::post('delete',[GradeController::class,'delete']);
});

Route::prefix('subjects')->group(function(){
    Route::post('/',[SubjectController::class,'index']);
    Route::post('store',[SubjectController::class,'store']);
    Route::post('show',[SubjectController::class,'show']);
    Route::post('action',[SubjectController::class,'handleAction']);
});

Route::prefix('weekly-schedule')->group(function () {
    Route::post('section',[WeeklyScheduleController::class,'bySection']);
    Route::post('store', [WeeklyScheduleController::class, 'store']);
});

Route::prefix('daily-schedule')->group(function () {
    Route::post('section',[DailyScheduleController::class,'bySection']);
    Route::post('by-teacher',[DailyScheduleController::class,'byTeacherAcademicYear']);
    Route::post('store', [DailyScheduleController::class, 'store']);
});

Route::prefix('student-enrollment')->group(function(){
    Route::post('academic-year',[StudentEnrollmentController::class,'byAcademicYear']);
    Route::post('student',[StudentEnrollmentController::class,'byStudent']);
    Route::post('store',[StudentEnrollmentController::class,'store']);
    Route::post('update',[StudentEnrollmentController::class,'update']);
    Route::post('action',[StudentEnrollmentController::class,'handleAction']);
});

Route::prefix('attendance')->group(function(){
    Route::post('schedule',[AcademicAttendanceController::class,'bySchedule']);
    Route::post('store',[AcademicAttendanceController::class,'store']);
    // Route::post('record-attendance',[AttendanceController::class,'recordAttendance']);
    // Route::post('get-attendance',[AttendanceController::class,'getAttendance']);
    // Route::post('create',[AttendanceController::class,'create']);
    // Route::post('by-schedule',[AttendanceController::class,'bySchedule']);
});

// Route::prefix('exams')->group(function(){
//     Route::post('list',[ExamController::class,'list']);
//     Route::post('create',[ExamController::class,'create']);
//     Route::post('detail',[ExamController::class,'detail']);
//     Route::put('update',[ExamController::class,'update']);
//     Route::post('delete',[ExamController::class,'delete']);
// });

// Route::prefix('exam-schedules')->group(function(){
//     Route::post('list',[ExamScheduleController::class,'list']);
//     Route::post('create',[ExamScheduleController::class,'create']);
//     Route::post('detail',[ExamScheduleController::class,'detail']);
//     Route::put('update',[ExamScheduleController::class,'update']);
//     Route::post('delete',[ExamScheduleController::class,'delete']);
// });

// Route::prefix('holidays')->group(function(){
//     Route::post('list',[HolidayController::class,'list']);
//     Route::post('create',[HolidayController::class,'create']);
//     Route::post('detail',[HolidayController::class,'detail']);
//     Route::put('update',[HolidayController::class,'update']);
//     Route::post('delete',[HolidayController::class,'delete']);
// });

// Route::prefix('certificates')->group(function(){
//     Route::post('add-certificate',[CertificateController::class,'addCertificate']);
// });