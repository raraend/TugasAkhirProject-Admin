<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitor_contents', function (Blueprint $table) {
            $table->id(); // primary key
            $table->string('id_departments');
            $table->unsignedBigInteger('content_id');
            $table->boolean('is_visible_to_parent')->default(false);

            $table->timestamps();

            // Relasi
            $table->foreign('id_departments')
                  ->references('id_departments')
                  ->on('departments')
                  ->onDelete('cascade');

            $table->foreign('content_id')
                  ->references('id')
                  ->on('contents')
                  ->onDelete('cascade');

            // Unique constraint agar tidak duplikat per konten & departemen
            $table->unique(['id_departments', 'content_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitor_contents');
    }
};
