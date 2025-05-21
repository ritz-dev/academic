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

Route::prefix('academic')->group(function(){
    Route::post('/',[AcademicClassSectionController::class,'index']);
    // Route::post('list-post',[AcademicYearController::class,'list']);
    // Route::post('create',[AcademicYearController::class,'create']);
    // Route::post('detail',[AcademicYearController::class,'detail']);
    // Route::put('update',[AcademicYearController::class,'update']);
    // Route::post('delete',[AcademicYearController::class,'delete']);
});

Route::prefix('academic-years')->group(function(){
    Route::post('/',[AcademicYearController::class,'index']);
    Route::post('list-post',[AcademicYearController::class,'list']);
    Route::post('create',[AcademicYearController::class,'create']);
    Route::post('detail',[AcademicYearController::class,'detail']);
    Route::put('update',[AcademicYearController::class,'update']);
    Route::post('delete',[AcademicYearController::class,'delete']);
});

Route::prefix('classes')->group(function(){
    Route::post('/',[AcademicClassController::class,'index']);
    Route::post('list-post',[AcademicClassController::class,'list']);
    Route::post('create',[AcademicClassController::class,'create']);
    Route::post('detail',[AcademicClassController::class,'detail']);
    Route::put('update',[AcademicClassController::class,'update']);
    Route::post('delete',[AcademicClassController::class,'delete']);
    Route::post('by-year',[AcademicClassController::class,'byYear']);
});

Route::prefix('sections')->group(function(){
    Route::post('/',[SectionController::class,'index']);
    Route::post('create',[SectionController::class,'create']);
    Route::post('detail',[SectionController::class,'detail']);
    Route::put('update',[SectionController::class,'update']);
    Route::post('delete',[SectionController::class,'delete']);
    Route::post('by-class',[SectionController::class,'byClass']);
    Route::post('students-in-section',[SectionController::class,'StudentsInSection']);
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
    Route::post('create',[SubjectController::class,'create']);
    Route::post('detail',[SubjectController::class,'detail']);
    Route::put('update',[SubjectController::class,'update']);
    Route::post('delete',[SubjectController::class,'delete']);
    Route::post('by-section',[SubjectController::class,'bySection']);
    Route::post('not-in-section',[SubjectController::class,'notInSection']);
    Route::post('add-subject',[SubjectController::class,'addSubject']);
});

Route::prefix('weekly-schedule')->group(function () {
    Route::post('section',[WeeklyScheduleController::class,'bySection']);
    // Route::post('list', [ScheduleController::class, 'list']);
    // Route::post('create', [ScheduleController::class, 'create']);
    // Route::post('by-section',[ScheduleController::class,'bySection']);
    // Route::post('/', [ScheduleController::class, 'store']);
    // Route::put('/{id}', [ScheduleController::class, 'update']);
    // Route::delete('/{id}', [ScheduleController::class, 'destroy']);
});

Route::prefix('daily-schedule')->group(function () {
    Route::post('section',[DailyScheduleController::class,'bySection']);
});

Route::prefix('student-enrollment')->group(function(){
    Route::post('academic-year',[StudentEnrollmentController::class,'byAcademicYear']);
    Route::post('create',[StudentEnrollmentController::class,'create']);
});

Route::prefix('attendance')->group(function(){
    Route::post('schedule',[AcademicAttendanceController::class,'bySchedule']);
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

// Route::prefix('timetables')->group(function(){
//     Route::post('list',[TimeTableController::class,'list']);
//     Route::post('create',[TimeTableController::class,'create']);
//     Route::post('detail',[TimeTableController::class,'detail']);
//     Route::put('update',[TimeTableController::class,'update']);
//     Route::put('update-date-time',[TimeTableController::class,'updateDateTime']);
//     Route::post('delete',[TimeTableController::class,'delete']);
//     Route::post('by-section',[TimeTableController::class,'bySection']);
//     Route::post('by-section-date',[TimeTableController::class,'bySectionDate']);
// });



// Route::prefix('holidays')->group(function(){
//     Route::post('list',[HolidayController::class,'list']);
//     Route::post('create',[HolidayController::class,'create']);
//     Route::post('detail',[HolidayController::class,'detail']);
//     Route::put('update',[HolidayController::class,'update']);
//     Route::post('delete',[HolidayController::class,'delete']);
// });

// Route::prefix('attendances')->group(function(){
//     Route::post('record-attendance',[AttendanceController::class,'recordAttendance']);
//     Route::post('get-attendance',[AttendanceController::class,'getAttendance']);
//     Route::post('create',[AttendanceController::class,'create']);
//     Route::post('by-schedule',[AttendanceController::class,'bySchedule']);
// });

// Route::prefix('certificates')->group(function(){
//     Route::post('add-certificate',[CertificateController::class,'addCertificate']);
// });