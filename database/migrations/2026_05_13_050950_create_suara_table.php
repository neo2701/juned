<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilu_id')->constrained('pemilu')->onDelete('cascade');
            $table->text('encrypted_vote');
            $table->enum('status', ['MASUK', 'TERVERIFIKASI', 'DITOLAK'])->default('MASUK');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suara');
    }
};
