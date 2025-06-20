<?php

namespace App\Models;

use Ramsey\Uuid\Guid\Guid;
use Illuminate\Database\Eloquent\Model;

class AssessmentResult extends Model
{
    protected $fillable = [
        'slug',
        'previous_hash',
        'hash',
        'assessment_id',
        'student_id',
        'marks_obtained',
        'remarks',
        'status',
        'graded_by',
        'graded_at',
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
    
    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
