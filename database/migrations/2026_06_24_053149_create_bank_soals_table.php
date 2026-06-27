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
        Schema::create('bank_soals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('mapel_id')->constrained('mapels')->onDelete('cascade'); // Menghubungkan ke tabel mapel
            $table->enum('kelas', ['7', '8', '9']); // Enum pilihan kelas 7, 8, dan 9
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_soals');
    }
};
