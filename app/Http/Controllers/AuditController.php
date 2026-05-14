<?php

namespace App\Http\Controllers;

use App\Models\Pemilu;
use App\Models\Suara;
use App\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class AuditController extends Controller
{
    public function __construct(
        private VerificationService $verificationService
    ) {
    }

    /**
     * GET /admin/pemilu/{pemilu}/audit — Show audit page with vote list.
     */
    public function index(Pemilu $pemilu)
    {
        $suaras = Suara::where('pemilu_id', $pemilu->id)
            ->with(['zkpProof', 'nullifier'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($suara) {
                return [
                    'id' => $suara->id,
                    'vote_hash' => $suara->vote_hash,
                    'nullifier_hash' => $suara->nullifier?->nullifier_hash,
                    'status' => $suara->status,
                    'proof_status' => $suara->zkpProof?->status_valid ?? 'BELUM_DIVERIFIKASI',
                    'verified_at' => $suara->zkpProof?->verified_at?->toDateTimeString(),
                    'created_at' => $suara->created_at?->toDateTimeString(),
                ];
            });

        $stats = [
            'total' => $suaras->count(),
            'verified' => $suaras->where('status', 'TERVERIFIKASI')->count(),
            'rejected' => $suaras->where('status', 'DITOLAK')->count(),
            'pending' => $suaras->where('status', 'MASUK')->count(),
        ];

        return Inertia::render('Admin/Pemilu/Audit', [
            'pemilu' => $pemilu,
            'suaras' => $suaras->values(),
            'stats' => $stats,
        ]);
    }

    /**
     * POST /admin/pemilu/{pemilu}/verify/{suara} — Verify single vote.
     */
    public function verifySingle(Pemilu $pemilu, Suara $suara): JsonResponse
    {
        if ($suara->pemilu_id !== $pemilu->id) {
            return response()->json(['error' => 'Vote does not belong to this election'], 403);
        }

        $result = $this->verificationService->verifySingle($suara->id);

        return response()->json($result);
    }

    /**
     * POST /admin/pemilu/{pemilu}/verify-all — Bulk verify all votes.
     */
    public function verifyAll(Pemilu $pemilu): JsonResponse
    {
        $result = $this->verificationService->verifyAll($pemilu->id);

        return response()->json($result);
    }

    /**
     * POST /admin/pemilu/{pemilu}/audit-tree — Audit Merkle Tree integrity.
     */
    public function auditTree(Pemilu $pemilu): JsonResponse
    {
        $result = $this->verificationService->auditMerkleTree($pemilu->id);

        return response()->json($result);
    }
}
