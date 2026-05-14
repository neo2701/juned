<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merkle_tree', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilu_id')->constrained('pemilu')->onDelete('cascade');
            $table->string('root_hash')->nullable();
            $table->string('status')->default('OPEN');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merkle_tree');
    }
};
