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
        Schema::table('ujian_siswas', function (Blueprint $table) {
            $table->json('urutan_soal')->nullable()->after('jawaban_siswa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujian_siswas', function (Blueprint $table) {
            $table->dropColumn('urutan_soal');
        });
    }
};
