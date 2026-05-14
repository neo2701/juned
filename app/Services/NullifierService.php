<?php

namespace App\Services;

use App\Models\Nullifier;

class NullifierService
{
    /**
     * Check if a nullifier already exists for a given election.
     *
     * The nullifier hash is computed client-side as Poseidon(privateKey, pemiluId)
     * by the zk-SNARK circuit and provided in the vote submission payload.
     */
    public function hasVoted(string $nullifierHash, int $pemiluId): bool
    {
        return Nullifier::where('pemilu_id', $pemiluId)
            ->where('nullifier_hash', $nullifierHash)
            ->exists();
    }

    /**
     * Store a nullifier hash for a given election.
     *
     * The nullifier hash is the decimal string representation of the
     * Poseidon(privateKey, pemiluId) output from the circuit's public signals.
     */
    public function store(string $nullifierHash, int $pemiluId): Nullifier
    {
        return Nullifier::create([
            'pemilu_id' => $pemiluId,
            'nullifier_hash' => $nullifierHash,
        ]);
    }
}
