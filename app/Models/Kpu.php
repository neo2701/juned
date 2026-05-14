<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kpu extends Model
{
    protected $table = 'kpu';

    protected $fillable = [
        'nama_instansi',
        'nama_petugas',
        'jabatan',
        'email',
    ];

    public function hasilPemilus(): HasMany
    {
        return $this->hasMany(HasilPemilu::class);
    }
}
