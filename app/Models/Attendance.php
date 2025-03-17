<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use SoftDeletes;

    protected $fillable = ['slug','timetable_id', 'attendee_id', 'attendee_type', 'status', 'date', 'remarks','hash','previous_hash'];

    protected $hidden = ["created_at","updated_at","deleted_at"];

    public function timetable()
    {
        return $this->belongsTo(TimeTable::class, 'timetable_id');
    }
}
