<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MerkleTree extends Model
{
    protected $table = 'merkle_tree';

    protected $fillable = [
        'pemilu_id',
        'root_hash',
        'total_leaf',
        'nodes_data',
        'status',
    ];

    protected $casts = [
        'nodes_data' => 'array',
    ];

    public function pemilu(): BelongsTo
    {
        return $this->belongsTo(Pemilu::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(MerkleLeaf::class);
    }

    public function auditVerifikasis(): HasMany
    {
        return $this->hasMany(AuditVerifikasi::class);
    }

    public function hasilPemilus(): HasMany
    {
        return $this->hasMany(HasilPemilu::class);
    }
}
