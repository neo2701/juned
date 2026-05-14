<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Nullifier extends Model
{
    protected $table = 'nullifier';

    protected $fillable = [
        'pemilu_id',
        'nullifier_hash',
        'is_used',
        'used_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
    ];

    public function pemilu(): BelongsTo
    {
        return $this->belongsTo(Pemilu::class);
    }

    public function suara(): HasOne
    {
        return $this->hasOne(Suara::class);
    }
}
