<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\WeeklySchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'teacher_id' => $this->faker->optional()->randomNumber(5),
            'weekly_schedule_id' => WeeklySchedule::inRandomOrder()->first()->id ?? WeeklySchedule::factory(),
            'date' => $this->faker->date(),
            'type' => $this->faker->randomElement(['class', 'holiday', 'exam']),
        ];
    }
}
