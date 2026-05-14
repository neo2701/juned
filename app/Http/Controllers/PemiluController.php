<?php

namespace App\Http\Controllers;

use App\Models\MerkleTree;
use App\Models\Pemilih;
use App\Models\Pemilu;
use App\Models\Suara;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PemiluController extends Controller
{
    public function index()
    {
        $pemilus = Pemilu::withCount(['kandidats', 'suaras', 'nullifiers'])->latest()->get();
        return Inertia::render('Admin/Pemilu/Index', ['pemilus' => $pemilus]);
    }

    public function show(Pemilu $pemilu)
    {
        $pemilu->load('kandidats');

        // Voter count
        $voterCount = Pemilih::count();

        // Merkle tree status
        $merkleTree = MerkleTree::where('pemilu_id', $pemilu->id)->first();

        // Vote statistics
        $totalVotes = Suara::where('pemilu_id', $pemilu->id)->count();
        $votesByStatus = Suara::where('pemilu_id', $pemilu->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $turnoutPercentage = $voterCount > 0 ? round(($totalVotes / $voterCount) * 100, 1) : 0;

        // --- Vote Tally: count verified votes per candidate from public_signals ---
        $verifiedVotes = Suara::where('pemilu_id', $pemilu->id)
            ->where('status', 'TERVERIFIKASI')
            ->with('zkpProof')
            ->get();

        $tally = [];
        foreach ($verifiedVotes as $suara) {
            $signals = json_decode($suara->zkpProof?->public_signals, true);
            if ($signals && isset($signals[3])) {
                $kandidatId = $signals[3];
                $tally[$kandidatId] = ($tally[$kandidatId] ?? 0) + 1;
            }
        }

        // Pending votes (MASUK) tally
        $pendingVotes = Suara::where('pemilu_id', $pemilu->id)
            ->where('status', 'MASUK')
            ->with('zkpProof')
            ->get();

        $pendingTally = [];
        foreach ($pendingVotes as $suara) {
            $signals = json_decode($suara->zkpProof?->public_signals, true);
            if ($signals && isset($signals[3])) {
                $kandidatId = $signals[3];
                $pendingTally[$kandidatId] = ($pendingTally[$kandidatId] ?? 0) + 1;
            }
        }

        // Map to candidate names
        $tallyResults = [];
        foreach ($pemilu->kandidats as $kandidat) {
            $tallyResults[] = [
                'id' => $kandidat->id,
                'nomor_urut' => $kandidat->nomor_urut,
                'nama_kandidat' => $kandidat->nama_kandidat,
                'votes' => $tally[(string) $kandidat->id] ?? 0,
                'pending' => $pendingTally[(string) $kandidat->id] ?? 0,
            ];
        }
        // Sort by votes descending
        usort($tallyResults, fn($a, $b) => $b['votes'] - $a['votes']);

        $totalVerified = array_sum(array_column($tallyResults, 'votes'));
        $totalPending = array_sum(array_column($tallyResults, 'pending'));

        return Inertia::render('Admin/Pemilu/Show', [
            'pemilu' => $pemilu,
            'stats' => [
                'voterCount' => $voterCount,
                'totalVotes' => $totalVotes,
                'votesByStatus' => $votesByStatus,
                'turnoutPercentage' => $turnoutPercentage,
                'merkleTree' => $merkleTree ? [
                    'status' => $merkleTree->status,
                    'root_hash' => $merkleTree->root_hash,
                    'created_at' => $merkleTree->created_at?->toDateTimeString(),
                ] : null,
            ],
            'tallyResults' => $tallyResults,
            'totalVerified' => $totalVerified,
            'totalPending' => $totalPending,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Pemilu/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tahun' => 'nullable|integer|min:2000|max:2100',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'description' => 'nullable|string',
            'status' => 'required|in:DRAFT,BERJALAN,SELESAI,DIPUBLIKASIKAN',
        ]);

        Pemilu::create($validated);

        return redirect()->route('admin.pemilu.index')->with('success', 'Pemilu created.');
    }

    public function edit(Pemilu $pemilu)
    {
        return Inertia::render('Admin/Pemilu/Edit', ['pemilu' => $pemilu]);
    }

    public function update(Request $request, Pemilu $pemilu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tahun' => 'nullable|integer|min:2000|max:2100',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'description' => 'nullable|string',
            'status' => 'required|in:DRAFT,BERJALAN,SELESAI,DIPUBLIKASIKAN',
        ]);

        $pemilu->update($validated);

        return redirect()->route('admin.pemilu.index')->with('success', 'Pemilu updated.');
    }

    public function destroy(Pemilu $pemilu)
    {
        $pemilu->delete();

        return redirect()->route('admin.pemilu.index')->with('success', 'Pemilu deleted.');
    }
}
