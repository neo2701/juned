<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('zkp_proof', function (Blueprint $table) {
            $table->text('public_signals')->nullable()->after('proof_data');
        });
    }

    public function down(): void
    {
        Schema::table('zkp_proof', function (Blueprint $table) {
            $table->dropColumn('public_signals');
        });
    }
};
