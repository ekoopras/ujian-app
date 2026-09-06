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
        Schema::create('ujian_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujians')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('waktu_mulai');
            $table->timestamp('waktu_selesai_seharusnya');
            $table->timestamp('waktu_submit')->nullable();
            $table->json('jawaban_siswa')->nullable(); // format: {"soal_id": "jawaban"}
            $table->json('ragu_siswa')->nullable()->after('jawaban_siswa');
            $table->enum('status', ['sedang_mengerjakan', 'selesai'])->default('sedang_mengerjakan');
            $table->boolean('is_rekaped')->default(false)->after('status');
            $table->timestamps();
        });

        // Rekap nilai permanen (menggunakan nullOnDelete / simpan teks nama agar tidak hilang saat Ujian dihapus)
        Schema::create('rekap_nilais', function (Blueprint $table) {
            $table->id();
            // nullOnDelete agar jika ID ujian di-delete, record rekap TIDAK terhapus
            $table->foreignId('ujian_id')->nullable()->constrained('ujians')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->nullOnDelete();

            // Backup informasi Ujian & Siswa dalam bentuk string
            $table->string('nama_mapel');
            $table->string('nama_siswa');
            $table->string('nama_kelas')->nullable();

            $table->integer('total_soal')->default(0);
            $table->integer('jawaban_benar')->default(0);
            $table->integer('jawaban_salah')->default(0);
            $table->decimal('nilai_akhir', 5, 2)->default(0);
            $table->string('tahun_ajaran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_nilais');
        Schema::dropIfExists('ujian_siswas');
    }
};
