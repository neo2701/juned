<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zkp_proof', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suara_id')->constrained('suara')->onDelete('cascade');
            $table->text('proof_data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zkp_proof');
    }
};
