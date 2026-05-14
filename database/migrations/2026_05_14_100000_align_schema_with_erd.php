<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // ─── pemilu: add tahun, tanggal_mulai, tanggal_selesai, expand status enum ───
        Schema::table('pemilu', function (Blueprint $table) {
            $table->year('tahun')->nullable()->after('name');
            $table->dateTime('tanggal_mulai')->nullable()->after('tahun');
            $table->dateTime('tanggal_selesai')->nullable()->after('tanggal_mulai');
        });

        // SQLite doesn't support ALTER COLUMN for check constraints, so we recreate the constraint
        // by adding a new check. For SQLite we use a raw statement to update the check.
        // Actually for SQLite, we'll handle the status expansion via application-level validation
        // and drop/recreate approach. Since Laravel SQLite doesn't support modifying enums,
        // we'll use a pragmatic approach: drop the old check and add new one.
        // In SQLite, CHECK constraints can't be altered. We'll handle this by recreating the column.
        // However, since this is complex in SQLite, we'll use DB::statement to work around it.

        // For SQLite: recreate pemilu table with new status check
        DB::statement('CREATE TABLE "pemilu_new" (
            "id" integer primary key autoincrement not null,
            "name" varchar not null,
            "tahun" integer,
            "tanggal_mulai" datetime,
            "tanggal_selesai" datetime,
            "description" text,
            "status" varchar check("status" in(\'DRAFT\', \'BERJALAN\', \'SELESAI\', \'DIPUBLIKASIKAN\')) not null default \'DRAFT\',
            "created_at" datetime,
            "updated_at" datetime
        )');

        DB::statement('INSERT INTO "pemilu_new" (id, name, tahun, tanggal_mulai, tanggal_selesai, description, status, created_at, updated_at)
            SELECT id, name, tahun, tanggal_mulai, tanggal_selesai, description, status, created_at, updated_at FROM "pemilu"');

        DB::statement('DROP TABLE "pemilu"');
        DB::statement('ALTER TABLE "pemilu_new" RENAME TO "pemilu"');

        // ─── kandidat: add nama_kandidat, status_aktif ───
        Schema::table('kandidat', function (Blueprint $table) {
            $table->string('nama_kandidat', 200)->nullable()->after('nomor_urut');
            $table->boolean('status_aktif')->default(true)->after('visi_misi');
        });

        // ─── pemilih: add nama_pemilih, identitas_hash, status_audit ───
        Schema::table('pemilih', function (Blueprint $table) {
            $table->string('nama_pemilih', 150)->nullable()->after('nik');
            $table->string('identitas_hash', 255)->nullable()->after('private_key_hash');
            $table->boolean('status_audit')->default(false)->after('identitas_hash');
        });

        // ─── nullifier: add is_used, used_at ───
        Schema::table('nullifier', function (Blueprint $table) {
            $table->boolean('is_used')->default(false)->after('nullifier_hash');
            $table->dateTime('used_at')->nullable()->after('is_used');
        });

        // ─── suara: add nullifier_id FK, vote_hash, waktu_suara ───
        Schema::table('suara', function (Blueprint $table) {
            $table->unsignedBigInteger('nullifier_id')->nullable()->after('pemilu_id');
            $table->string('vote_hash', 255)->nullable()->after('encrypted_vote');
            $table->dateTime('waktu_suara')->nullable()->after('vote_hash');

            $table->foreign('nullifier_id')->references('id')->on('nullifier')->onDelete('set null');
        });

        // ─── merkle_tree: add total_leaf, update status values ───
        Schema::table('merkle_tree', function (Blueprint $table) {
            $table->integer('total_leaf')->default(0)->after('root_hash');
        });

        // Update existing status values: OPEN → DRAFT, GENERATED → FINAL
        DB::table('merkle_tree')->where('status', 'OPEN')->update(['status' => 'DRAFT']);
        DB::table('merkle_tree')->where('status', 'GENERATED')->update(['status' => 'FINAL']);

        // ─── merkle_leaf: add posisi (alias for position, keep position for backward compat) ───
        // position already exists, ERD calls it posisi - we keep position column as-is

        // ─── zkp_proof: add proof_hash, status_valid, verified_at ───
        Schema::table('zkp_proof', function (Blueprint $table) {
            $table->string('proof_hash', 255)->nullable()->after('proof_data');
            $table->string('status_valid')->default('BELUM_DIVERIFIKASI')->after('proof_hash');
            $table->dateTime('verified_at')->nullable()->after('status_valid');
        });

        // ─── auditor: rename name→nama_auditor, credentials→nama_lembaga, add email ───
        Schema::table('auditor', function (Blueprint $table) {
            $table->renameColumn('name', 'nama_auditor');
            $table->renameColumn('credentials', 'nama_lembaga');
        });

        Schema::table('auditor', function (Blueprint $table) {
            $table->string('email', 150)->nullable()->after('nama_lembaga');
        });

        // ─── audit_verifikasi: change hasil_verifikasi to string enum, add catatan, verified_at ───
        // SQLite workaround: add new columns, migrate data, drop old
        Schema::table('audit_verifikasi', function (Blueprint $table) {
            $table->string('hasil_verifikasi_new')->nullable()->after('merkle_tree_id');
            $table->text('catatan')->nullable()->after('hasil_verifikasi_new');
            $table->dateTime('verified_at')->nullable()->after('catatan');
        });

        // Migrate existing boolean values to string enum
        DB::table('audit_verifikasi')->where('hasil_verifikasi', 1)->update(['hasil_verifikasi_new' => 'VALID']);
        DB::table('audit_verifikasi')->where('hasil_verifikasi', 0)->update(['hasil_verifikasi_new' => 'TIDAK_VALID']);

        Schema::table('audit_verifikasi', function (Blueprint $table) {
            $table->dropColumn('hasil_verifikasi');
        });

        Schema::table('audit_verifikasi', function (Blueprint $table) {
            $table->renameColumn('hasil_verifikasi_new', 'hasil_verifikasi');
        });

        // ─── NEW TABLE: kpu ───
        Schema::create('kpu', function (Blueprint $table) {
            $table->id();
            $table->string('nama_instansi', 150);
            $table->string('nama_petugas', 150);
            $table->string('jabatan', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->timestamps();
        });

        // ─── NEW TABLE: hasil_pemilu ───
        Schema::create('hasil_pemilu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemilu_id')->constrained('pemilu')->onDelete('cascade');
            $table->foreignId('merkle_tree_id')->constrained('merkle_tree')->onDelete('cascade');
            $table->foreignId('kpu_id')->constrained('kpu')->onDelete('cascade');
            $table->dateTime('tanggal_sah')->nullable();
            $table->dateTime('tanggal_publikasi')->nullable();
            $table->string('status_pengesahan')->default('DRAFT');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('hasil_pemilu');
        Schema::dropIfExists('kpu');

        // Revert audit_verifikasi
        Schema::table('audit_verifikasi', function (Blueprint $table) {
            $table->renameColumn('hasil_verifikasi', 'hasil_verifikasi_new');
        });

        Schema::table('audit_verifikasi', function (Blueprint $table) {
            $table->boolean('hasil_verifikasi')->nullable()->after('merkle_tree_id');
        });

        DB::table('audit_verifikasi')->where('hasil_verifikasi_new', 'VALID')->update(['hasil_verifikasi' => 1]);
        DB::table('audit_verifikasi')->where('hasil_verifikasi_new', 'TIDAK_VALID')->update(['hasil_verifikasi' => 0]);

        Schema::table('audit_verifikasi', function (Blueprint $table) {
            $table->dropColumn(['hasil_verifikasi_new', 'catatan', 'verified_at']);
        });

        // Revert auditor
        Schema::table('auditor', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('auditor', function (Blueprint $table) {
            $table->renameColumn('nama_auditor', 'name');
            $table->renameColumn('nama_lembaga', 'credentials');
        });

        // Revert zkp_proof
        Schema::table('zkp_proof', function (Blueprint $table) {
            $table->dropColumn(['proof_hash', 'status_valid', 'verified_at']);
        });

        // Revert merkle_tree status
        DB::table('merkle_tree')->where('status', 'DRAFT')->update(['status' => 'OPEN']);
        DB::table('merkle_tree')->where('status', 'FINAL')->update(['status' => 'GENERATED']);

        Schema::table('merkle_tree', function (Blueprint $table) {
            $table->dropColumn('total_leaf');
        });

        // Revert suara
        Schema::table('suara', function (Blueprint $table) {
            $table->dropForeign(['nullifier_id']);
            $table->dropColumn(['nullifier_id', 'vote_hash', 'waktu_suara']);
        });

        // Revert nullifier
        Schema::table('nullifier', function (Blueprint $table) {
            $table->dropColumn(['is_used', 'used_at']);
        });

        // Revert pemilih
        Schema::table('pemilih', function (Blueprint $table) {
            $table->dropColumn(['nama_pemilih', 'identitas_hash', 'status_audit']);
        });

        // Revert kandidat
        Schema::table('kandidat', function (Blueprint $table) {
            $table->dropColumn(['nama_kandidat', 'status_aktif']);
        });

        // Revert pemilu - recreate without new columns and with old status check
        DB::statement('CREATE TABLE "pemilu_old" (
            "id" integer primary key autoincrement not null,
            "name" varchar not null,
            "description" text,
            "status" varchar check("status" in(\'DRAFT\', \'BERJALAN\', \'SELESAI\')) not null default \'DRAFT\',
            "created_at" datetime,
            "updated_at" datetime
        )');

        DB::statement('INSERT INTO "pemilu_old" (id, name, description, status, created_at, updated_at)
            SELECT id, name, description, status, created_at, updated_at FROM "pemilu"');

        DB::statement('DROP TABLE "pemilu"');
        DB::statement('ALTER TABLE "pemilu_old" RENAME TO "pemilu"');
    }
};
