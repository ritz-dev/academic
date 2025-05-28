<?php

namespace App\Models;

use App\Models\Subject;
use Ramsey\Uuid\Guid\Guid;
use App\Models\AcademicClassSection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailySchedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'date',
        'academic_class_section_slug',
        'subject_slug',
        'teacher_slug',
        'teacher_name',
        'start_time',
        'end_time',
        'is_holiday',
        'type',
        'holiday_type',
        'status',
        'note',
        'academic_info'
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

    public function academicClassSection()
    {
        return $this->belongsTo(AcademicClassSection::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
