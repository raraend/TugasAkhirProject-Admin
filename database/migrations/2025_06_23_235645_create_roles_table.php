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
        Schema::create('roles', function (Blueprint $table) {
            $table->string('id_roles', 4)->primary(); // ID role custom, misalnya RL01 = Superadmin, RL02 = Admin.
            $table->string('name_roles', 50); // Nama roleny
            $table->timestamps(); // created_at & updated_at, penting buat tracking data juga.

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
