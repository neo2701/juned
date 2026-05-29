<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pemilih', function (Blueprint $table) {
            $table->string('private_key_hash')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pemilih', function (Blueprint $table) {
            $table->string('private_key_hash')->nullable(false)->change();
        });
    }
};
