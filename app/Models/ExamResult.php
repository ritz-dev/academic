<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamResult extends Model
{
    use SoftDeletes;

    protected $fillable = ['student_id','exam_schedules_id','mark','grade', 'date','hash','previous_hash'];

    protected $hidden = ["created_at","updated_at","deleted_at"];

    public function examSchedule()
    {
        return $this->belongsTo(ExamSchedule::class);
    }
}
