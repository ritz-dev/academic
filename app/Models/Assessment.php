<?php

namespace App\Models;

use Ramsey\Uuid\Guid\Guid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'title',
        'teacher_slug',
        'academic_class_section_slug',
        'subject_slug',
        'type',
        'date',
        'due_date',
        'max_marks',
        'min_marks',
        'description',
        'is_published',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
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

    // If you have a Teacher model:
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_slug', 'slug');
    }
}