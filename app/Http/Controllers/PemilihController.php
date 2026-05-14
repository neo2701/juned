<?php

namespace App\Http\Controllers;

use App\Models\Pemilih;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class PemilihController extends Controller
{
    public function index()
    {
        $pemilihs = Pemilih::latest()->paginate(10);
        return Inertia::render('Admin/Pemilih/Index', [
            'pemilihs' => $pemilihs,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Pemilih/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|string|unique:pemilih,nik|size:16',
            'nama_pemilih' => 'nullable|string|max:150',
        ]);

        // Generate a SNARK-friendly keypair (BN254 field private key and Poseidon commitment)
        $output = shell_exec('node ' . base_path('scripts/generate_voter.js'));
        $keypair = json_decode($output, true);

        if (!$keypair || !isset($keypair['private_key']) || !isset($keypair['commitment'])) {
            return back()->withErrors(['nik' => 'Failed to generate cryptographic keys. Make sure Node.js and dependencies are installed.']);
        }

        Pemilih::create([
            'nik' => $request->nik,
            'nama_pemilih' => $request->nama_pemilih,
            'private_key_hash' => $keypair['commitment'],
        ]);

        return redirect()->route('admin.pemilih.index')->with([
            'success' => 'Voter registered successfully.',
            'new_private_key' => $keypair['private_key'],
            'new_voter_nik' => $request->nik
        ]);
    }

    public function destroy(Pemilih $pemilih)
    {
        $pemilih->delete();
        return redirect()->route('admin.pemilih.index')->with('success', 'Voter deleted.');
    }
}
