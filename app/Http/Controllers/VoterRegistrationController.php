<?php

namespace App\Http\Controllers;

use App\Models\Pemilih;
use App\Services\MerkleTreeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
     * Demo page: show pre-approved NIKs that are not registered yet.
     */
    public function demoPreapproved()   
    {
        $preapproved = Pemilih::query()
            ->where('registration_status', 'APPROVED')
            ->whereNull('private_key_hash')
            ->orderBy('nik')
            ->get(['nik', 'nama_pemilih', 'created_at']);

        return Inertia::render('Voter/PreapprovedDemo', [
            'preapprovedNik' => $preapproved,
        ]);
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
    public function store(Request $request, MerkleTreeService $merkleTreeService)
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

        try {
            $merkleTreeService->regenerateTreesForEligibleElections();
        } catch (\Throwable $exception) {
            Log::error('Failed to regenerate Merkle tree after voter registration: ' . $exception->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Registrasi berhasil, tetapi pembaruan Merkle Tree gagal. Silakan coba regenerasi tree secara manual.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil. Simpan private key Anda dengan aman.',
        ]);
    }
}
