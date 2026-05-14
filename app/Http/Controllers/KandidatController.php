<?php

namespace App\Http\Controllers;

use App\Models\Kandidat;
use App\Models\Pemilu;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KandidatController extends Controller
{
    public function index(Pemilu $pemilu)
    {
        $kandidats = $pemilu->kandidats()->orderBy('nomor_urut')->get();
        return Inertia::render('Admin/Kandidat/Index', [
            'pemilu' => $pemilu,
            'kandidats' => $kandidats
        ]);
    }

    public function create(Pemilu $pemilu)
    {
        return Inertia::render('Admin/Kandidat/Create', ['pemilu' => $pemilu]);
    }

    public function store(Request $request, Pemilu $pemilu)
    {
        $validated = $request->validate([
            'nomor_urut' => 'required|integer|min:1',
            'nama_kandidat' => 'nullable|string|max:200',
            'visi_misi' => 'required|string',
            'status_aktif' => 'boolean',
        ]);

        $pemilu->kandidats()->create($validated);

        return redirect()->route('admin.pemilu.kandidat.index', $pemilu)->with('success', 'Kandidat created.');
    }

    public function edit(Pemilu $pemilu, Kandidat $kandidat)
    {
        return Inertia::render('Admin/Kandidat/Edit', [
            'pemilu' => $pemilu,
            'kandidat' => $kandidat
        ]);
    }

    public function update(Request $request, Pemilu $pemilu, Kandidat $kandidat)
    {
        $validated = $request->validate([
            'nomor_urut' => 'required|integer|min:1',
            'nama_kandidat' => 'nullable|string|max:200',
            'visi_misi' => 'required|string',
            'status_aktif' => 'boolean',
        ]);

        $kandidat->update($validated);

        return redirect()->route('admin.pemilu.kandidat.index', $pemilu)->with('success', 'Kandidat updated.');
    }

    public function destroy(Pemilu $pemilu, Kandidat $kandidat)
    {
        $kandidat->delete();

        return redirect()->route('admin.pemilu.kandidat.index', $pemilu)->with('success', 'Kandidat deleted.');
    }
}
