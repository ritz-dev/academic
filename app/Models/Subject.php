<?php

namespace App\Models;

use App\Models\AcademicClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes;

    protected $fillable = ["slug","name","code","description","academic_class_id"];

    protected $hidden = ["created_at","updated_at","deleted_at"];

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class,"academic_class_id","id");
    }
}
