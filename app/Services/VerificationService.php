<?php

namespace App\Services;

use App\Models\MerkleTree;
use App\Models\Suara;
use App\Models\ZkpProof;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;

class VerificationService
{
    /**
     * Verify a single vote's proof by invoking scripts/verify.js.
     * Updates zkp_proof.status_valid and zkp_proof.verified_at.
     * Updates suara.status to TERVERIFIKASI or DITOLAK.
     */
    public function verifySingle(int $suaraId): array
    {
        $zkpProof = ZkpProof::where('suara_id', $suaraId)->first();

        if (!$zkpProof) {
            return [
                'success' => false,
                'error' => 'ZKP proof not found for this vote',
            ];
        }

        $suara = Suara::find($suaraId);

        if (!$suara) {
            return [
                'success' => false,
                'error' => 'Vote record not found',
            ];
        }

        // Parse proof data
        $proof = json_decode($zkpProof->proof_data, true);

        if (!$proof) {
            $zkpProof->update([
                'status_valid' => 'TIDAK_VALID',
                'verified_at' => now(),
            ]);
            $suara->update(['status' => 'DITOLAK']);

            return [
                'success' => false,
                'error' => 'Invalid proof data format',
            ];
        }

        // Parse public signals
        $publicSignals = json_decode($zkpProof->public_signals, true);

        if (!$publicSignals || !is_array($publicSignals) || count($publicSignals) !== 4) {
            // Don't mark as DITOLAK — the vote may be legitimate but was cast
            // before public_signals storage was implemented
            return [
                'success' => false,
                'error' => 'Cannot re-verify: public signals were not stored with this vote. The vote was accepted at submission time but cannot be re-audited.',
            ];

            return [
                'success' => false,
                'error' => 'Invalid or missing public signals',
            ];
        }

        // Invoke scripts/verify.js
        $vkeyPath = public_path('zkp/vkey.json');

        $input = json_encode([
            'proof' => $proof,
            'publicSignals' => $publicSignals,
            'vkeyPath' => $vkeyPath,
        ]);

        $process = new Process([config('app.node_path'), base_path('scripts/verify.js')]);
        $process->setInput($input);
        $process->setTimeout(15);

        try {
            $process->run();
        } catch (\Symfony\Component\Process\Exception\ProcessTimedOutException $e) {
            Log::error("Verification timeout for suara_id: {$suaraId}");

            return [
                'success' => false,
                'error' => 'Verification process timed out',
            ];
        }

        $output = $process->getOutput();
        $result = json_decode($output, true);

        if (!$process->isSuccessful() || $result === null) {
            $errorMsg = $result['error'] ?? 'Verification script failed';
            Log::error("Verification failed for suara_id: {$suaraId} - {$errorMsg}");

            $zkpProof->update([
                'status_valid' => 'TIDAK_VALID',
                'verified_at' => now(),
            ]);
            $suara->update(['status' => 'DITOLAK']);

            return [
                'success' => false,
                'error' => $errorMsg,
            ];
        }

        $isValid = !empty($result['valid']);

        $zkpProof->update([
            'status_valid' => $isValid ? 'VALID' : 'TIDAK_VALID',
            'verified_at' => now(),
        ]);

        $suara->update([
            'status' => $isValid ? 'TERVERIFIKASI' : 'DITOLAK',
        ]);

        return [
            'success' => true,
            'valid' => $isValid,
            'suara_id' => $suaraId,
            'status' => $isValid ? 'VALID' : 'TIDAK_VALID',
        ];
    }

    /**
     * Bulk verify all votes for an election.
     * Returns summary: { total, valid, invalid, errors }
     */
    public function verifyAll(int $pemiluId): array
    {
        $suaras = Suara::where('pemilu_id', $pemiluId)
            ->whereHas('zkpProof')
            ->pluck('id');

        $total = $suaras->count();
        $valid = 0;
        $invalid = 0;
        $errors = 0;

        foreach ($suaras as $suaraId) {
            $result = $this->verifySingle($suaraId);

            if ($result['success'] && $result['valid']) {
                $valid++;
            } elseif ($result['success'] && !$result['valid']) {
                $invalid++;
            } else {
                $errors++;
            }
        }

        return [
            'total' => $total,
            'valid' => $valid,
            'invalid' => $invalid,
            'errors' => $errors,
        ];
    }

    /**
     * Audit Merkle Tree integrity — recompute root from leaves.
     * Returns { valid: bool, computed_root: string, stored_root: string }
     */
    public function auditMerkleTree(int $pemiluId): array
    {
        $merkleTree = MerkleTree::where('pemilu_id', $pemiluId)
            ->where('status', 'FINAL')
            ->first();

        if (!$merkleTree) {
            return [
                'valid' => false,
                'error' => 'Merkle Tree not found or not finalized for this election',
                'computed_root' => null,
                'stored_root' => null,
            ];
        }

        // Get all leaves ordered by position
        $leaves = $merkleTree->leaves()
            ->orderBy('position')
            ->pluck('hash')
            ->toArray();

        if (empty($leaves)) {
            return [
                'valid' => false,
                'error' => 'No leaves found in the Merkle Tree',
                'computed_root' => null,
                'stored_root' => $merkleTree->root_hash,
            ];
        }

        // Invoke scripts/merkle_tree.js to recompute the root
        $input = json_encode([
            'leaves' => $leaves,
            'depth' => 10,
        ]);

        $process = new Process([config('app.node_path'), base_path('scripts/merkle_tree.js')]);
        $process->setInput($input);
        $process->setTimeout(30);

        try {
            $process->run();
        } catch (\Symfony\Component\Process\Exception\ProcessTimedOutException $e) {
            return [
                'valid' => false,
                'error' => 'Merkle Tree computation timed out',
                'computed_root' => null,
                'stored_root' => $merkleTree->root_hash,
            ];
        }

        if (!$process->isSuccessful()) {
            $output = json_decode($process->getOutput(), true);
            $errorMsg = $output['error'] ?? 'Merkle Tree script failed';

            return [
                'valid' => false,
                'error' => $errorMsg,
                'computed_root' => null,
                'stored_root' => $merkleTree->root_hash,
            ];
        }

        $result = json_decode($process->getOutput(), true);

        if (!$result || !isset($result['root'])) {
            return [
                'valid' => false,
                'error' => 'Invalid output from merkle_tree.js',
                'computed_root' => null,
                'stored_root' => $merkleTree->root_hash,
            ];
        }

        $computedRoot = $result['root'];
        $storedRoot = $merkleTree->root_hash;
        $isValid = $computedRoot === $storedRoot;

        return [
            'valid' => $isValid,
            'computed_root' => $computedRoot,
            'stored_root' => $storedRoot,
        ];
    }
}
