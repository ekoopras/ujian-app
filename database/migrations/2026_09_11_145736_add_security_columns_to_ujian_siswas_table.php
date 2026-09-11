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
            $table->unsignedInteger('jumlah_pelanggaran')->default(0)->after('status');
            $table->boolean('is_locked')->default(false)->after('jumlah_pelanggaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujian_siswas', function (Blueprint $table) {
            $table->dropColumn(['jumlah_pelanggaran', 'is_locked']);
        });
    }
};
