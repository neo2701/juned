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
        'registration_status',
        'registration_token',
        'registered_at',
    ];

    protected $hidden = [
        'private_key_hash',
        'identitas_hash',
        'registration_token',
    ];

    protected $casts = [
        'status_audit' => 'boolean',
        'registered_at' => 'datetime',
    ];

    public function isApproved(): bool
    {
        return $this->registration_status === 'APPROVED';
    }

    public function isRegistered(): bool
    {
        return $this->registration_status === 'REGISTERED';
    }
}
