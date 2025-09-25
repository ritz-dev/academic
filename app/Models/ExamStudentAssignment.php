<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamStudentAssignment extends Model
{
    protected $fillable = ['exam_schedule_id','student_id','status'];

    protected $hidden = ["created_at","updated_at","deleted_at"];

    public function examSchedule()
    {
        return $this->belongsTo(ExamSchedule::class);
    }
}
