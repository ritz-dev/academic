<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'teacher_id',
        'weekly_schedule_id',
        'date',
        'type',
    ];

    /**
     * Define the relationship with WeeklySchedule.
     */
    public function weeklySchedule()
    {
        return $this->belongsTo(WeeklySchedule::class, 'weekly_schedule_id');
    }
}
