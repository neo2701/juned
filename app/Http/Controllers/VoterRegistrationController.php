<?php

namespace App\Http\Controllers;

use App\Models\Pemilih;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class VoterRegistrationController extends Controller
{
    /**
     * Show the self-registration page.
     * Voter enters their NIK to check eligibility.
     */
    public function create()
    {
        return Inertia::render('Voter/Register');
    }

    /**
     * Verify NIK eligibility for self-registration.
     * Returns status so the frontend knows whether to show key generation.
     */
    public function checkNik(Request $request)
    {
        $request->validate([
            'nik' => 'required|string|size:16',
        ]);

        $pemilih = Pemilih::where('nik', $request->nik)->first();

        if (!$pemilih) {
            return response()->json([
                'eligible' => false,
                'reason' => 'NIK tidak terdaftar dalam daftar pemilih yang disetujui.',
            ]);
        }

        if ($pemilih->registration_status === 'REGISTERED') {
            return response()->json([
                'eligible' => false,
                'reason' => 'NIK ini sudah terdaftar. Silakan login untuk melanjutkan.',
            ]);
        }

        return response()->json([
            'eligible' => true,
            'nama' => $pemilih->nama_pemilih,
        ]);
    }

    /**
     * Complete self-registration: receive the commitment (Poseidon hash of private key)
     * generated client-side. The server never sees the private key.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|string|size:16',
            'commitment' => 'required|string|regex:/^\d+$/',
        ]);

        $pemilih = Pemilih::where('nik', $request->nik)
            ->where('registration_status', 'APPROVED')
            ->first();

        if (!$pemilih) {
            return response()->json([
                'success' => false,
                'error' => 'NIK tidak eligible untuk registrasi.',
            ], 422);
        }

        // Store the commitment — this is the Poseidon(privateKey) computed client-side
        $pemilih->update([
            'private_key_hash' => $request->commitment,
            'registration_status' => 'REGISTERED',
            'registration_token' => null,
            'registered_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil. Simpan private key Anda dengan aman.',
        ]);
    }
}
