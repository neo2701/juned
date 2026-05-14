<?php

namespace App\Http\Controllers;

use App\Models\Pemilu;
use App\Models\Suara;
use App\Models\ZkpProof;
use App\Services\NullifierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class SuaraController extends Controller
{
    public function __construct(
        private NullifierService $nullifierService
    ) {
    }

    /**
     * GET /voter/api/elections
     *
     * Return elections with status BERJALAN and their candidates.
     */
    public function elections(): JsonResponse
    {
        $elections = Pemilu::where('status', 'BERJALAN')
            ->with('kandidats')
            ->get();

        return response()->json($elections);
    }

    /**
     * POST /voter/api/vote
     *
     * Validate, verify proof, check nullifier, and persist vote atomically.
     */
    public function store(Request $request): JsonResponse
    {
        // Step 1: Validate request payload
        $validated = $request->validate([
            'proof' => ['required', 'array'],
            'publicSignals' => ['required', 'array', 'size:4'],
            'publicSignals.*' => ['required', 'string'],
            'encrypted_vote' => ['required', 'string'],
            'nullifier_hash' => ['required', 'string'],
            'pemilu_id' => ['required', 'integer'],
        ]);

        $pemiluId = $validated['pemilu_id'];
        $proof = $validated['proof'];
        $publicSignals = $validated['publicSignals'];
        $encryptedVote = $validated['encrypted_vote'];
        $nullifierHash = $validated['nullifier_hash'];

        // Step 2: Verify election status is BERJALAN
        $pemilu = Pemilu::find($pemiluId);

        if (!$pemilu || $pemilu->status !== 'BERJALAN') {
            return response()->json([
                'error' => 'Election is not active',
            ], 403);
        }

        // Step 3: Invoke scripts/verify.js via Process with proof data on stdin
        $vkeyPath = public_path('zkp/vkey.json');

        $input = json_encode([
            'proof' => $proof,
            'publicSignals' => $publicSignals,
            'vkeyPath' => $vkeyPath,
        ]);

        $process = new Process(['node', base_path('scripts/verify.js')]);
        $process->setInput($input);
        $process->setTimeout(10);

        try {
            $process->run();
        } catch (\Symfony\Component\Process\Exception\ProcessTimedOutException $e) {
            return response()->json([
                'error' => 'Proof verification timed out',
            ], 500);
        }

        // Step 4: Parse verifier output
        $output = $process->getOutput();
        $result = json_decode($output, true);

        if (!$process->isSuccessful() || $result === null) {
            return response()->json([
                'error' => 'Proof verification failed',
            ], 422);
        }

        if (empty($result['valid'])) {
            return response()->json([
                'error' => 'Proof verification failed',
            ], 422);
        }

        // Step 5: Confirm nullifier from publicSignals[0] matches submitted nullifier_hash
        if ($publicSignals[0] !== $nullifierHash) {
            return response()->json([
                'error' => 'Nullifier hash mismatch',
            ], 422);
        }

        // Step 6: Check nullifier uniqueness
        if ($this->nullifierService->hasVoted($nullifierHash, $pemiluId)) {
            return response()->json([
                'error' => 'Voter has already voted in this election',
            ], 409);
        }

        // Step 7: Atomic DB transaction
        try {
            DB::transaction(function () use ($pemiluId, $encryptedVote, $proof, $publicSignals, $nullifierHash) {
                $nullifier = $this->nullifierService->store($nullifierHash, $pemiluId);

                $suara = Suara::create([
                    'pemilu_id' => $pemiluId,
                    'nullifier_id' => $nullifier->id,
                    'encrypted_vote' => $encryptedVote,
                    'vote_hash' => hash('sha256', $encryptedVote . $nullifierHash . $pemiluId),
                    'waktu_suara' => now(),
                    'status' => 'MASUK',
                ]);

                ZkpProof::create([
                    'suara_id' => $suara->id,
                    'proof_data' => json_encode($proof),
                    'public_signals' => json_encode($publicSignals),
                    'proof_hash' => hash('sha256', json_encode($proof)),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Vote transaction failed: ' . $e->getMessage());

            return response()->json([
                'error' => 'Vote could not be recorded',
            ], 500);
        }

        // Step 8: Return success
        return response()->json([
            'message' => 'Vote recorded successfully',
        ], 201);
    }
}
