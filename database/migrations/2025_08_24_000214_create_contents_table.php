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
        Schema::create('contents', function (Blueprint $table) {
            $table->id(); // Primary key auto-increment
            $table->string('title', 255); // Judul konten
            $table->string('description', 255); // Deskripsi konten
            $table->string('file_original', 255); // Nama file asli dari user
            $table->string('file_server', 20); // Nama file setelah diubah saat disimpan di server
            $table->integer('duration'); // Durasi tayang (dalam detik/menit sesuai kebutuhan)
            $table->date('start_date'); // Tanggal mulai tayang
            $table->date('end_date'); // Tanggal akhir tayang
            $table->time('start_time'); // Jam mulai tayang
            $table->time('end_time'); // Jam akhir tayang
            $table->string('repeat_days'); // Hari tayang, misal "1,2,3,4,5"

            $table->unsignedBigInteger('modified_by'); // ID user yang terakhir mengubah
            $table->unsignedBigInteger('created_by'); // ID user yang membuat konten
            


            // Relasi ke users
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('modified_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
