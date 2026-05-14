<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemilih', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->unique();
            $table->string('private_key_hash');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemilih');
    }
};
