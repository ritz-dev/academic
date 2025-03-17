<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamTeacherAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = ['exam_schedule_id','teacher_id','role','status'];

    protected $hidden = ["created_at","updated_at","deleted_at"];

    public function examSchedule()
    {
        return $this->belongsTo(ExamSchedule::class);
    }
}
