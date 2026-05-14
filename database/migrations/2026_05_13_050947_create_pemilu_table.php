<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemilu', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['DRAFT', 'BERJALAN', 'SELESAI'])->default('DRAFT');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemilu');
    }
};
