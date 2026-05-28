<?php

namespace App\Http\Controllers;

use App\Models\MerkleTree;
use App\Models\Pemilu;
use App\Models\Suara;
use Inertia\Inertia;

class PublicAuditController extends Controller
{
    /**
     * GET /audit — Public audit dashboard listing all elections.
     */
    public function index()
    {
        $pemilus = Pemilu::withCount(['suaras', 'kandidats'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($pemilu) {
                $verified = Suara::where('pemilu_id', $pemilu->id)
                    ->where('status', 'TERVERIFIKASI')->count();
                $rejected = Suara::where('pemilu_id', $pemilu->id)
                    ->where('status', 'DITOLAK')->count();
                $merkleTree = MerkleTree::where('pemilu_id', $pemilu->id)
                    ->where('status', 'FINAL')->first();

                return [
                    'id' => $pemilu->id,
                    'name' => $pemilu->name,
                    'description' => $pemilu->description,
                    'status' => $pemilu->status,
                    'tahun' => $pemilu->tahun,
                    'total_votes' => $pemilu->suaras_count,
                    'verified_votes' => $verified,
                    'rejected_votes' => $rejected,
                    'pending_votes' => $pemilu->suaras_count - $verified - $rejected,
                    'candidates_count' => $pemilu->kandidats_count,
                    'merkle_root' => $merkleTree?->root_hash,
                    'merkle_status' => $merkleTree ? 'FINAL' : null,
                    'created_at' => $pemilu->created_at?->toDateTimeString(),
                ];
            });

        return Inertia::render('PublicAudit/Index', [
            'pemilus' => $pemilus,
        ]);
    }

    /**
     * GET /audit/{pemilu} — Public audit detail for a specific election.
     */
    public function show(Pemilu $pemilu)
    {
        // Get all votes (anonymized — no voter identity)
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

        // Merkle Tree info
        $merkleTree = MerkleTree::where('pemilu_id', $pemilu->id)
            ->where('status', 'FINAL')
            ->first();

        $merkleInfo = null;
        if ($merkleTree) {
            $merkleInfo = [
                'root_hash' => $merkleTree->root_hash,
                'total_leaf' => $merkleTree->total_leaf,
                'status' => $merkleTree->status,
                'created_at' => $merkleTree->created_at?->toDateTimeString(),
            ];
        }

        // Candidates with vote counts (only for finished/published elections)
        $results = null;
        if (in_array($pemilu->status, ['SELESAI', 'DIPUBLIKASIKAN'])) {
            $results = $pemilu->kandidats->map(function ($kandidat) use ($pemilu) {
                $votes = Suara::where('pemilu_id', $pemilu->id)
                    ->where('status', 'TERVERIFIKASI')
                    ->whereHas('nullifier', function ($q) use ($kandidat) {
                        // We can't directly link votes to candidates due to encryption
                        // This is a placeholder — real tally comes from encrypted_vote decryption
                    })
                    ->count();

                return [
                    'id' => $kandidat->id,
                    'nomor_urut' => $kandidat->nomor_urut,
                    'nama_kandidat' => $kandidat->nama_kandidat,
                ];
            });
        }

        return Inertia::render('PublicAudit/Show', [
            'pemilu' => [
                'id' => $pemilu->id,
                'name' => $pemilu->name,
                'description' => $pemilu->description,
                'status' => $pemilu->status,
                'tahun' => $pemilu->tahun,
            ],
            'suaras' => $suaras->values(),
            'stats' => $stats,
            'merkleInfo' => $merkleInfo,
        ]);
    }
}
