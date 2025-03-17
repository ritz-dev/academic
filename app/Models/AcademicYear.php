<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use SoftDeletes;

    protected $fillable = ['slug','year','start_date','end_date','stauts'];

    protected $hidden = ["created_at","updated_at","deleted_at"];

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
}
