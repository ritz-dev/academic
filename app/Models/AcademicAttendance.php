<?php

namespace App\Models;

use Ramsey\Uuid\Guid\Guid;
use App\Models\WeeklySchedule;
use Illuminate\Database\Eloquent\Model;

class AcademicAttendance extends Model
{
    protected $fillable = [
        'slug',
        'previous_hash',
        'hash',
        'weekly_schedule_slug',
        'subject',
        'academic_class_section_slug',
        'academic_info',
        'attendee_slug',
        'attendee_name',
        'attendee_type',
        'attendance_type',
        'approved_slug',
        'status',
        'date',
        'remark',
    ];

    protected $hidden = ["id","created_at","updated_at","deleted_at"];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if(empty($model->slug)){
                $model->slug = (string) Guid::uuid4();
            }
        });
    }

    public function weeklySchedule()
    {
        return $this->belongsTo(WeeklySchedule::class, 'weekly_schedule_slug', 'slug');
    }

    public function academicClassSection()
    {
        return $this->belongsTo(AcademicClassSection::class, 'academic_class_section_slug', 'slug');
    }


}
