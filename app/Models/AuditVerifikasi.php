<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditVerifikasi extends Model
{
    protected $table = 'audit_verifikasi';

    protected $fillable = [
        'auditor_id',
        'merkle_tree_id',
        'hasil_verifikasi',
        'catatan',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(Auditor::class);
    }

    public function merkleTree(): BelongsTo
    {
        return $this->belongsTo(MerkleTree::class);
    }
}
