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
        Schema::create('soals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_soal_id')->constrained('bank_soals')->cascadeOnDelete();
            $table->enum('jenis_soal', ['pilihan_ganda', 'pilihan_ganda_kompleks', 'menjodohkan', 'benar_salah'])->default('pilihan_ganda');
            $table->longText('pertanyaan');
            $table->string('gambar_soal')->nullable();
            $table->json('pilihan_jawaban'); // Menyimpan opsi-opsi jawaban & gambar jawaban
            $table->string('kunci_jawaban');
            $table->integer('bobot_nilai')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soals');
    }
};
