<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pemilih', function (Blueprint $table) {
            // APPROVED = NIK pre-approved by admin, awaiting voter self-registration
            // REGISTERED = Voter has completed self-registration (commitment submitted)
            $table->string('registration_status', 20)->default('REGISTERED')->after('nama_pemilih');
            $table->string('registration_token', 64)->nullable()->unique()->after('registration_status');
            $table->timestamp('registered_at')->nullable()->after('registration_token');
        });

        // Update existing records to REGISTERED status
        \Illuminate\Support\Facades\DB::table('pemilih')
            ->whereNotNull('private_key_hash')
            ->update(['registration_status' => 'REGISTERED', 'registered_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('pemilih', function (Blueprint $table) {
            $table->dropColumn(['registration_status', 'registration_token', 'registered_at']);
        });
    }
};
