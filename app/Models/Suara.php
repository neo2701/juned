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
        'encrypted_vote',
        'status',
    ];

    public function pemilu(): BelongsTo
    {
        return $this->belongsTo(Pemilu::class);
    }

    public function zkpProof(): HasOne
    {
        return $this->hasOne(ZkpProof::class);
    }
}
