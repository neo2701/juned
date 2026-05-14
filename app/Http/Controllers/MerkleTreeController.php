<?php

namespace App\Http\Controllers;

use App\Models\Pemilu;
use App\Services\MerkleTreeService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class MerkleTreeController extends Controller
{
    public function __construct(
        private MerkleTreeService $merkleTreeService
    ) {
    }

    /**
     * GET /voter/api/merkle-tree/{pemilu}
     *
     * Return the Merkle Tree leaves and root for client-side proof computation.
     */
    public function show(int $pemilu): JsonResponse
    {
        $election = Pemilu::find($pemilu);

        if (!$election) {
            return response()->json(['error' => 'Election not found'], 404);
        }

        if ($election->status !== 'BERJALAN') {
            return response()->json(['error' => 'Election is not active'], 403);
        }

        try {
            $treeData = $this->merkleTreeService->getTreeData($pemilu);
        } catch (RuntimeException $e) {
            return response()->json(['error' => 'Merkle Tree not available'], 404);
        }

        return response()->json($treeData);
    }

    /**
     * POST /admin/pemilu/{pemilu}/generate-tree
     *
     * Admin triggers Merkle Tree generation for an election.
     */
    public function generate(int $pemilu): JsonResponse
    {
        $election = Pemilu::find($pemilu);

        if (!$election) {
            return response()->json(['error' => 'Election not found'], 404);
        }

        try {
            $merkleTree = $this->merkleTreeService->buildTree($pemilu);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Merkle Tree generated successfully',
            'root' => $merkleTree->root_hash,
        ]);
    }
}
