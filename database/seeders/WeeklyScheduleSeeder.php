<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WeeklySchedule;

class WeeklyScheduleSeeder extends Seeder
{
    public function run(): void
    {
        WeeklySchedule::factory()->count(10)->create();
    }
}
