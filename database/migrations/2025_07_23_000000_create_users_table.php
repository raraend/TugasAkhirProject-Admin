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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Primary key auto-increment
            $table->string('role_id', 4); // ID role, foreign key ke roles (contoh: RO01)
            $table->string('id_departments', 4); // ID departemen user, foreign key ke departments
            $table->string('name_user', 50); // Nama pengguna
            $table->string('password', 255); // Password terenkripsi
            $table->string('email', 50); // Email user
            $table->rememberToken(); 
            $table->timestamps(); // Kolom created_at dan updated_at

            // Relasi ke roles
            $table->foreign('role_id')->references('id_roles')->on('roles')->onDelete('cascade');
            
            // Relasi ke departments
            $table->foreign('id_departments')->references('id_departments')->on('departments')->onDelete('cascade');
        });


        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
