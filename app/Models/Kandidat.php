<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kandidat extends Model
{
    protected $table = 'kandidat';
    
    protected $fillable = [
        'pemilu_id',
        'nomor_urut',
        'visi_misi'
    ];

    public function pemilu(): BelongsTo
    {
        return $this->belongsTo(Pemilu::class);
    }
}
