<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Employee extends Authenticatable
{
    use HasApiTokens,Notifiable,HasFactory,HasUuids,SoftDeletes;

    protected $table = "employees";
    protected $fillable = [
        "personal_id",
        "email",
        "phonenumber",
        "password",
        "role_id",
        "department",
        "salary",
        "hire_date",
        "status",
        "employment_type"
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
