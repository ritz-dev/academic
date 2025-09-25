<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "slug" => $this->slug,
            "examId" => $this->exam_id,
            "sectionId" => $this->section_id,
            "subject" => $this->subject,
            "date" => $this->date,
            "startTime" => $this->start_time,
            "endTime" => $this->end_time
        ];
    }
}
