<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemilih extends Model
{
    protected $table = 'pemilih';
    
    protected $fillable = [
        'nik',
        'private_key_hash'
    ];

    protected $hidden = [
        'private_key_hash'
    ];
}
