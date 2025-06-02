<?php

namespace App\Models;

use App\Models\Section;
use Ramsey\Uuid\Guid\Guid;
use App\Models\AcademicYear;
use App\Models\AcademicClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicClassSection extends Model
{
    //
    use SoftDeletes;

    protected $fillable = ['slug','academic_year_slug', 'class_slug', 'section_slug'];

    protected $hidden = ["id", 'academic_year_slug', 'class_slug', 'section_slug', "created_at", "updated_at", "deleted_at"];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if(empty($model->slug)){
                $model->slug = (string) Guid::uuid4();
            }
        });
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_slug', 'slug');
    }

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class, 'class_slug', 'slug');
    }

    public function academicSection()
    {
        return $this->belongsTo(Section::class, 'section_slug', 'slug');
    }

}
