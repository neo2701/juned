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
