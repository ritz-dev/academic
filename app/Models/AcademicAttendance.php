<?php

namespace App\Models;

use Ramsey\Uuid\Guid\Guid;
use App\Models\WeeklySchedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicAttendance extends Model
{
    use SoftDeletes;

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

    public function schedule()
    {
        return $this->belongsTo(WeeklySchedule::class, 'weekly_schedule_slug', 'slug');
    }

    public function academicClassSection()
    {
        return $this->belongsTo(AcademicClassSection::class, 'academic_class_section_slug', 'slug');
    }

    public function attendee()
    {
        return $this->morphTo(__FUNCTION__, 'attendee_type', 'attendee_slug');
    }
}
