<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerkleLeaf extends Model
{
    protected $table = 'merkle_leaf';

    protected $fillable = [
        'merkle_tree_id',
        'suara_id',
        'hash',
        'position',
        'parent_hash',
    ];

    public function merkleTree(): BelongsTo
    {
        return $this->belongsTo(MerkleTree::class);
    }

    public function suara(): BelongsTo
    {
        return $this->belongsTo(Suara::class);
    }
}
