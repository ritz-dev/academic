<?php

namespace Database\Factories;

use App\Models\WeeklySchedule;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class WeeklyScheduleFactory extends Factory
{
    protected $model = WeeklySchedule::class;

    public function definition(): array
    {
        return [
            'section_id' => Section::inRandomOrder()->first()->id ?? Section::factory(),
            'subject_id' => $this->faker->optional()->randomNumber(5),
            'day' => $this->faker->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
            'start_time' => $this->faker->time('H:i:s'),
            'end_time' => $this->faker->time('H:i:s'),
            'is_break' => $this->faker->boolean(20), // 20% chance of being true
        ];
    }
}
