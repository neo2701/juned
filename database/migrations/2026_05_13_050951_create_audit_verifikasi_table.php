<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_verifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auditor_id')->constrained('auditor')->onDelete('cascade');
            $table->foreignId('merkle_tree_id')->constrained('merkle_tree')->onDelete('cascade');
            $table->boolean('hasil_verifikasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_verifikasi');
    }
};
