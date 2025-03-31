<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeeklySchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'section_id',
        'subject_id',
        'day',
        'start_time',
        'end_time',
        'is_break'
    ];

    /**
     * Define the relationship with the Section model.
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
