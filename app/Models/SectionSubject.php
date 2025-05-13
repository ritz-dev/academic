<?php

namespace App\Models;

use App\Models\Subject;
use Ramsey\Uuid\Guid\Guid;
use App\Models\AcademicClassSection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SectionSubject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',
    ];
    
    protected $hidden = ["id","academic_class_section_id","subject_id","created_at","updated_at","deleted_at"];
   
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
