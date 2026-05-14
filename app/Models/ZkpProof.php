<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZkpProof extends Model
{
    protected $table = 'zkp_proof';

    protected $fillable = [
        'suara_id',
        'proof_data',
        'public_signals',
        'proof_hash',
        'status_valid',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function suara(): BelongsTo
    {
        return $this->belongsTo(Suara::class);
    }
}
