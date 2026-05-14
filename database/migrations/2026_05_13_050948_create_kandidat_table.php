<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kandidat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilu_id')->constrained('pemilu')->onDelete('cascade');
            $table->integer('nomor_urut');
            $table->text('visi_misi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kandidat');
    }
};
