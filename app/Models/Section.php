<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use SoftDeletes;

    protected $fillable = ['slug','name','limit','teacher_id','academic_class_id'];

    protected $hidden = ["created_at","updated_at","deleted_at"];

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class);
    }
}
