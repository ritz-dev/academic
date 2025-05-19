<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Holiday;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            [
                "slug" => Str::uuid(),
                "name" => "New Year's Day",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Independence Day",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Chinese New Year",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Union Day",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Peasants' Day",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Full Moon Day of Tabaung",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Armed Forces Day",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Thingyan Water Festival",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Myanmar New Year",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Labour Day",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Full Moon Day of Kasong",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Full Moon Day of Waso",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Martyrs' Day",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Full Moon Day of Thadingyut",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Full Moon Day of Tazaungmone",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "National Day",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Kayin New Year",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Christmas Day",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Deepavali Day",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                "slug" => Str::uuid(),
                "name" => "Sabbath Day",
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]
        ];

        Holiday::insert($holidays);
    }
}
