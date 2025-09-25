<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificateResource extends JsonResource
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
            'studentId' => $this->student_id,
            'certificateType' => $this->certificate_type,
            'issueDate' => $this->issue_date,
            'expiryDate' => $this->expiry_date,
            'issuedBy' => $this->issued_by,
            'result' => $this->result,
            'academicYearId' => $this->academic_year_id,
            'additionalDetails' => $this->additional_details,
        ];
    }
}
