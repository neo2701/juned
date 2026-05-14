<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pemilu extends Model
{
    protected $table = 'pemilu';

    protected $fillable = [
        'name',
        'tahun',
        'tanggal_mulai',
        'tanggal_selesai',
        'description',
        'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function kandidats(): HasMany
    {
        return $this->hasMany(Kandidat::class);
    }

    public function suaras(): HasMany
    {
        return $this->hasMany(Suara::class);
    }

    public function merkleTree(): HasOne
    {
        return $this->hasOne(MerkleTree::class);
    }

    public function nullifiers(): HasMany
    {
        return $this->hasMany(Nullifier::class);
    }

    public function hasilPemilus(): HasMany
    {
        return $this->hasMany(HasilPemilu::class);
    }
}
