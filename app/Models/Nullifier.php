<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nullifier extends Model
{
    protected $table = 'nullifier';

    protected $fillable = [
        'pemilu_id',
        'nullifier_hash',
    ];

    public function pemilu(): BelongsTo
    {
        return $this->belongsTo(Pemilu::class);
    }
}
