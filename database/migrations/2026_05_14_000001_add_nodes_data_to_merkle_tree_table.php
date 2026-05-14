<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('merkle_tree', function (Blueprint $table) {
            $table->longText('nodes_data')->nullable()->after('root_hash');
        });
    }

    public function down(): void
    {
        Schema::table('merkle_tree', function (Blueprint $table) {
            $table->dropColumn('nodes_data');
        });
    }
};
