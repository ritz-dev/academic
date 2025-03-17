<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Models\ExamResult;
use App\Services\BlockChainService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class ExamResultController extends Controller
{
    protected $blockchainService;

    public function __construct(BlockChainService $blockchainService)
    {
        $this->blockchainService = $blockchainService;
    }

    public function addExamResult(Request $request)
    {
        try{

            $request->validate([
                'student_id' => 'required|exists:students,id',
                'exam_schedules_id' => 'required|exists:exam_schedules,id',
                'subject' => 'required|string',
                'mark' => 'required|integer',
                'grade' => 'required|string',
                'date' => 'required|date',
            ]);

            $previousHash = $this->blockchainService->getPreviousHash(ExamResult::class);
            $timestamp = now();
            $calculatedHash = $this->blockchainService->calculateHash(
                $previousHash,
                json_encode($request->all()),
                $timestamp->format('Y-m-d H:i:s')
            );

            $resultData = $request->all();
            $resultData['previous_hash'] = $previousHash;
            $resultData['hash'] = $calculatedHash;

            $result = ExamResult::create($resultData);

            return response()->json($result,200);

        }catch (Exception $e) {
            return $this->handleException($e, 'Failed to create certificate');
        }
    }

    public function verifyExamResult(Request $request)
    {
        // Find the ExamResult by its ID
        $examResult = ExamResult::findOrFail($request->id);

        if (!$examResult) {
            return response()->json(['message' => 'ExamResult not found'], 404);
        }

        // Convert timestamp to Carbon instance if it's stored as a string
        $timestamp = Carbon::parse($examResult->timestamp);

        // Recalculate hash based on ExamResult data
        $calculatedHash = $this->blockchainService->calculateHash(
            $examResult->previous_hash,
            json_encode($examResult),
            $timestamp->format('Y-m-d H:i:s') // Ensure timestamp is formatted correctly
        );

        // Compare the calculated hash with the stored hash
        if ($calculatedHash !== $examResult->hash) {
            return response()->json(['message' => 'ExamResult has been tampered with'], 400);
        }

        // Check if the previous hash matches the previous ExamResult's hash (if it exists)
        if ($examResult->previous_hash === '0000000000000000000000000000000000000000000000000000000000000000') {
            return response()->json(['message' => 'ExamResult is valid and verified'], 200);
        }

        $previousExamResult = ExamResult::where('hash', $examResult->previous_hash)->first();

        if ($previousExamResult) {
            return response()->json(['message' => 'ExamResult is valid and verified'], 200);
        }

        return response()->json(['message' => 'Invalid ExamResult chain'], 400);
    }
}