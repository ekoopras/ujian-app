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
        Schema::create('opsi_soal_ujians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soal_ujian_id')->constrained('soal_ujians')->onDelete('cascade');
            $table->text('text_jawaban')->nullable();
            $table->string('gambar_jawaban')->nullable();
            $table->text('kunci_pasangan')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('nilai')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opsi_soal_ujians');
    }
};
