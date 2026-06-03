<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merkle_leaf', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merkle_tree_id')->constrained('merkle_tree')->onDelete('cascade');
            $table->foreignId('suara_id')->nullable()->constrained('suara')->onDelete('set null');
            $table->string('hash');
            $table->string('parent_hash')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merkle_leaf');
    }
};
