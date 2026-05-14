<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilPemilu extends Model
{
    protected $table = 'hasil_pemilu';

    protected $fillable = [
        'pemilu_id',
        'merkle_tree_id',
        'kpu_id',
        'tanggal_sah',
        'tanggal_publikasi',
        'status_pengesahan',
        'catatan',
    ];

    protected $casts = [
        'tanggal_sah' => 'datetime',
        'tanggal_publikasi' => 'datetime',
    ];

    public function pemilu(): BelongsTo
    {
        return $this->belongsTo(Pemilu::class);
    }

    public function merkleTree(): BelongsTo
    {
        return $this->belongsTo(MerkleTree::class);
    }

    public function kpu(): BelongsTo
    {
        return $this->belongsTo(Kpu::class);
    }
}
