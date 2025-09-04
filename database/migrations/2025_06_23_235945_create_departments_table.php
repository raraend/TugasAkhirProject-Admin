<?php

use App\Models\Department;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentsTable extends Migration
{
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->string('id_departments', 4)->primary(); // ID unik untuk departemen, misalnya DP00, DP01. Bukan auto increment, jadi bisa custom.
            $table->uuid('uuid')->unique()->nullable(); // UUID opsional, bisa dipakai kalau mau sistem tracking atau share link.
            $table->string('name_departments', 255); // Nama lengkap departemennya, misal 'Fakultas Teknik'.
            $table->string('parent_id', 5)->nullable(); // Ini buat bikin struktur parent-child. Misal Prodi TI punya parent Fakultas Teknik.
            $table->timestamps(); // created_at & updated_at biar tahu kapan dibuat dan diubah.
            
            $table->foreign('parent_id')
                ->references('id_departments')->on('departments')
                ->onDelete('cascade'); // Kalau parent dihapus, semua anaknya ikut kehapus juga.
        });
    }

    public function down()
    {
        Schema::dropIfExists('departments'); // Buat rollback. Kalau migrate:rollback, ini yang akan dijalankan.

    }
}

