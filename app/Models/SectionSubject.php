<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SectionSubject extends Model
{
    use SoftDeletes;

    protected $table = "sections_subjects";

    protected $fillable = [
        "section_id",
        "subject_id",
    ];
}
