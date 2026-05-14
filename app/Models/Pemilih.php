<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemilih extends Model
{
    protected $table = 'pemilih';

    protected $fillable = [
        'nik',
        'nama_pemilih',
        'private_key_hash',
        'identitas_hash',
        'status_audit',
    ];

    protected $hidden = [
        'private_key_hash',
        'identitas_hash',
    ];

    protected $casts = [
        'status_audit' => 'boolean',
    ];
}
