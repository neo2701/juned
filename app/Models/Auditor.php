<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auditor extends Model
{
    protected $table = 'auditor';

    protected $fillable = [
        'nama_auditor',
        'nama_lembaga',
        'email',
    ];

    public function auditVerifikasis(): HasMany
    {
        return $this->hasMany(AuditVerifikasi::class);
    }
}
