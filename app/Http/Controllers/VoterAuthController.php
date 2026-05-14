<?php

namespace App\Http\Controllers;

use App\Models\Pemilih;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class VoterAuthController extends Controller
{
    public function create()
    {
        return Inertia::render('Voter/Login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
            'private_key' => 'required|string',
        ]);

        $pemilih = Pemilih::where('nik', $request->nik)->first();

        if (!$pemilih) {
            throw ValidationException::withMessages([
                'nik' => __('The provided credentials do not match our records.'),
            ]);
        }

        // Verify the private key using Poseidon
        $escapedKey = escapeshellarg($request->private_key);
        $computedHash = trim(shell_exec('node ' . base_path('scripts/poseidon_hash.js') . ' ' . $escapedKey));

        if ($computedHash !== $pemilih->private_key_hash) {
            throw ValidationException::withMessages([
                'nik' => __('The provided credentials do not match our records.'),
            ]);
        }

        // Store voter session securely
        session(['voter_id' => $pemilih->id]);
        $request->session()->regenerate();

        return redirect()->route('voter.dashboard');
    }

    public function destroy(Request $request)
    {
        $request->session()->forget('voter_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
