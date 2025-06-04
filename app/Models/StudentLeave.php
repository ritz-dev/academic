<?php

namespace App\Models;

use Ramsey\Uuid\Guid\Guid;
use App\Models\WeeklySchedule;
use App\Models\StudentEnrollment;
use App\Models\AcademicClassSection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentLeave extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'student_enrollment_slug',
        'academic_class_section_slug',
        'weekly_schedule_slug',
        'student_name',
        'academic_info',
        'date',
        'leave_type',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $hidden = ["id","created_at","updated_at","deleted_at"];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if(empty($model->slug)){
                $model->slug = (string) Guid::uuid4();
            }
        });
    }

    // Relationships
    public function enrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_slug', 'slug');
    }

    public function schedule()
    {
        return $this->belongsTo(WeeklySchedule::class, 'weekly_schedule_slug', 'slug');
    }

    public function classSection()
    {
        return $this->belongsTo(AcademicClassSection::class, 'academic_class_section_slug', 'slug');
    }
}
