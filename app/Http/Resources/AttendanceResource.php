<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'timetableId' => $this->timetable_id,
            'attendeeId' => $this->attendee_id,
            'attendeeType' => $this->attendee_type,
            'status' => $this->status,
            'date' => $this->date,
        ];
    }
}
