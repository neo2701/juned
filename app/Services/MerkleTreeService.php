<?php

namespace App\Services;

use App\Models\MerkleLeaf;
use App\Models\MerkleTree;
use App\Models\Pemilih;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Process\Process;

class MerkleTreeService
{
    private const TREE_DEPTH = 10;
    private const MAX_LEAVES = 1024; // 2^10

    /**
     * Build the Merkle Tree for an election.
     *
     * Collects all voter commitments, pads to 1024, delegates to Node.js for Poseidon hashing.
     *
     * @throws RuntimeException If voter count exceeds 1024 or Node.js script fails.
     */
    public function buildTree(int $pemiluId): MerkleTree
    {
        // Collect all voter commitments (private_key_hash) from pemilih table
        $commitments = Pemilih::pluck('private_key_hash')->toArray();

        if (count($commitments) > self::MAX_LEAVES) {
            throw new RuntimeException(
                'Voter count (' . count($commitments) . ') exceeds maximum tree capacity of ' . self::MAX_LEAVES
            );
        }

        // Prepare input for the Node.js Merkle Tree builder
        $input = json_encode([
            'leaves' => $commitments,
            'depth' => self::TREE_DEPTH,
        ]);

        // Invoke scripts/merkle_tree.js via Symfony Process
        $process = new Process(['node', base_path('scripts/merkle_tree.js')]);
        $process->setInput($input);
        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful()) {
            $output = json_decode($process->getOutput(), true);
            $errorMessage = $output['error'] ?? $process->getErrorOutput() ?: 'Merkle tree generation failed';
            throw new RuntimeException($errorMessage);
        }

        $result = json_decode($process->getOutput(), true);

        if (!$result || !isset($result['root']) || !isset($result['nodes'])) {
            throw new RuntimeException('Invalid output from merkle_tree.js script');
        }

        // Store in database within a transaction
        return DB::transaction(function () use ($pemiluId, $result, $commitments) {
            // Create or update the merkle_tree record
            $merkleTree = MerkleTree::updateOrCreate(
                ['pemilu_id' => $pemiluId],
                [
                    'root_hash' => $result['root'],
                    'total_leaf' => count($commitments),
                    'nodes_data' => $result['nodes'],
                    'status' => 'FINAL',
                ]
            );

            // Delete existing leaves for this tree (in case of regeneration)
            $merkleTree->leaves()->delete();

            // Store all leaves (level 0 = the 1024 leaf nodes) in merkle_leaf table
            $leaves = $result['nodes'][0]; // Level 0 contains all 1024 leaves

            $leafRecords = [];
            $now = now();

            foreach ($leaves as $position => $hash) {
                $leafRecords[] = [
                    'merkle_tree_id' => $merkleTree->id,
                    'suara_id' => null,
                    'hash' => $hash,
                    'position' => $position,
                    'parent_hash' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Insert in chunks to avoid memory issues
            foreach (array_chunk($leafRecords, 100) as $chunk) {
                MerkleLeaf::insert($chunk);
            }

            return $merkleTree;
        });
    }

    /**
     * Get the Merkle Proof (path elements + path indices) for a given commitment.
     *
     * @throws RuntimeException If commitment not found in the tree.
     */
    public function getProof(int $pemiluId, string $commitment): array
    {
        $merkleTree = MerkleTree::where('pemilu_id', $pemiluId)
            ->where('status', 'FINAL')
            ->first();

        if (!$merkleTree) {
            throw new RuntimeException('Merkle Tree not available for this election');
        }

        $nodes = $merkleTree->nodes_data;

        if (!$nodes || !is_array($nodes)) {
            throw new RuntimeException('Merkle Tree nodes data is not available');
        }

        // Find the leaf index for the given commitment
        $leaves = $nodes[0]; // Level 0 = leaves
        $leafIndex = array_search($commitment, $leaves);

        if ($leafIndex === false) {
            throw new RuntimeException('Voter commitment not found in the Merkle Tree');
        }

        // Compute path elements and path indices
        $pathElements = [];
        $pathIndices = [];

        $currentIndex = $leafIndex;

        for ($level = 0; $level < self::TREE_DEPTH; $level++) {
            // Path index at this level: which side is the current node on (0 = left, 1 = right)
            $pathIndex = $currentIndex & 1;
            $pathIndices[] = $pathIndex;

            // Sibling is at the opposite position
            $siblingIndex = $pathIndex === 0 ? $currentIndex + 1 : $currentIndex - 1;
            $pathElements[] = $nodes[$level][$siblingIndex];

            // Move to parent index for next level
            $currentIndex = intdiv($currentIndex, 2);
        }

        return [
            'pathElements' => $pathElements,
            'pathIndices' => $pathIndices,
        ];
    }

    /**
     * Get all leaves (ordered by position) and root hash for an election's Merkle Tree.
     *
     * @throws RuntimeException If tree not found.
     */
    public function getTreeData(int $pemiluId): array
    {
        $merkleTree = MerkleTree::where('pemilu_id', $pemiluId)
            ->where('status', 'FINAL')
            ->first();

        if (!$merkleTree) {
            throw new RuntimeException('Merkle Tree not available for this election');
        }

        $leaves = MerkleLeaf::where('merkle_tree_id', $merkleTree->id)
            ->orderBy('position')
            ->pluck('hash')
            ->toArray();

        return [
            'leaves' => $leaves,
            'root' => $merkleTree->root_hash,
        ];
    }
}
