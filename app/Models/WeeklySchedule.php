<?php

namespace App\Models;

use Ramsey\Uuid\Guid\Guid;
use Illuminate\Database\Eloquent\Model;

class WeeklySchedule extends Model
{
    protected $fillable = [
        'slug',
        'academic_class_section_slug',
        'subject_slug',
        'subject_name',
        'teacher_slug',
        'teacher_name',
        'day_of_week',
        'start_time',
        'end_time',
        'type',
        'academic_info'
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


    public function academicClassSection()
    {
        return $this->belongsTo(AcademicClassSection::class, 'academic_class_section_slug', 'slug');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_slug', 'slug');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
    
}
