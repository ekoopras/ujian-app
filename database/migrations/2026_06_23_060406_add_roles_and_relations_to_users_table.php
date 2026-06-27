<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'guru', 'pengawas', 'siswa'])
                ->default('siswa')
                ->after('email');
            $table->foreignId('mapel_id')
                ->nullable()
                ->constrained('mapels')
                ->nullOnDelete()
                ->after('role');
            $table->foreignId('kelas_id')
                ->nullable()
                ->constrained('kelases')
                ->nullOnDelete()
                ->after('mapel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['mapel_id']);
            $table->dropForeign(['kelas_id']);
            $table->dropColumn(['role', 'mapel_id', 'kelas_id']);
        });
    }
};
