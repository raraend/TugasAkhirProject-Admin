<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('monitor_contents', function (Blueprint $table) {
            $table->boolean('is_tayang_request')->default(false)->after('is_visible_to_parent');
        });
    }

    public function down(): void
    {
        Schema::table('monitor_contents', function (Blueprint $table) {
            $table->dropColumn('is_tayang_request');
        });
    }

};
