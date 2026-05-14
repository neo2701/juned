<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nullifier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilu_id')->constrained('pemilu')->onDelete('cascade');
            $table->string('nullifier_hash');
            $table->timestamps();
            $table->unique(['pemilu_id', 'nullifier_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nullifier');
    }
};
