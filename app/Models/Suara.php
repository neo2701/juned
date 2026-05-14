<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Suara extends Model
{
    protected $table = 'suara';

    protected $fillable = [
        'pemilu_id',
        'nullifier_id',
        'encrypted_vote',
        'vote_hash',
        'waktu_suara',
        'status',
    ];

    protected $casts = [
        'waktu_suara' => 'datetime',
    ];

    public function pemilu(): BelongsTo
    {
        return $this->belongsTo(Pemilu::class);
    }

    public function nullifier(): BelongsTo
    {
        return $this->belongsTo(Nullifier::class);
    }

    public function zkpProof(): HasOne
    {
        return $this->hasOne(ZkpProof::class);
    }
}
