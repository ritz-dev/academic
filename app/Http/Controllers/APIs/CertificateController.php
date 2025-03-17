<?php

namespace App\Http\Controllers\APIs;

use Exception;
use Carbon\Carbon;
use App\Models\Certificate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\BlockChainService;
use App\Http\Controllers\Controller;
use App\Http\Resources\CertificateResource;

class CertificateController extends Controller
{
    protected $blockchainService;

    public function __construct(BlockChainService $blockchainService)
    {
        $this->blockchainService = $blockchainService;
    }

    public function addCertificate(Request $request)
    {
        try{

            $request->validate([
                'studentId' => 'required',
                'certificateType' => 'required|string',
                'issueDate' => 'required|date',
                'expiryDate' => 'nullable|date',
                'issuedBy' => 'required|string',
                'result' => 'required|string',
                'academicYearId' => 'required|exists:academic_years,id',
                'additionalDetails' => 'nullable|string',
                Rule::unique('certificates') ->where(fn ($query) => $query
                ->where('student_id', $request->studentId)
                ->where('certificate_type', $request->certificateType)
                )
            ]);

            $previousHash = $this->blockchainService->getPreviousHash(Certificate::class);
            $timestamp = now();
            $calculatedHash = $this->blockchainService->calculateHash(
                $previousHash,
                json_encode($request->all()),
                $timestamp->format('Y-m-d H:i:s')
            );

            $certificateData = $request->all();
            $certificateData['previous_hash'] = $previousHash;
            $certificateData['hash'] = $calculatedHash;

            $certificate = new Certificate;
            $certificate->slug = Str::uuid();
            $certificate->student_id = $request->studentId;
            $certificate->certificate_type = $request->certificateType;
            $certificate->issue_date = $request->issueDate;
            $certificate->expiry_date = $request->expiryDate;
            $certificate->issued_by = $request->issuedBy;
            $certificate->result = $request->result;
            $certificate->academic_year_id = $request->academicYearId;
            $certificate->additional_details = $request->additionalDetails;
            $certificate->save();

            $certificate = new CertificateResource($certificate);

            return response()->json($certificate,200);

        }catch (Exception $e) {
            return $this->handleException($e, 'Failed to create certificate');
        }
    }

    public function verifyCertificate(Request $request)
    {
        // Find the Certificate by its ID
        $certificate = Certificate::findOrFail($request->id);

        if (!$certificate) {
            return response()->json(['message' => 'Certificate not found'], 404);
        }

        // Convert timestamp to Carbon instance if it's stored as a string
        $timestamp = Carbon::parse($certificate->timestamp);

        // Recalculate hash based on Certificate data
        $calculatedHash = $this->blockchainService->calculateHash(
            $certificate->previous_hash,
            json_encode($certificate),
            $timestamp->format('Y-m-d H:i:s') // Ensure timestamp is formatted correctly
        );

        // Compare the calculated hash with the stored hash
        if ($calculatedHash !== $certificate->hash) {
            return response()->json(['message' => 'Certificate has been tampered with'], 400);
        }

        // Check if the previous hash matches the previous Certificate's hash (if it exists)
        if ($certificate->previous_hash === '0000000000000000000000000000000000000000000000000000000000000000') {
            return response()->json(['message' => 'Certificate is valid and verified'], 200);
        }

        $previousCertificate = Certificate::where('hash', $certificate->previous_hash)->first();

        if ($previousCertificate) {
            return response()->json(['message' => 'Certificate is valid and verified'], 200);
        }

        return response()->json(['message' => 'Invalid Certificate chain'], 400);
    }

}
