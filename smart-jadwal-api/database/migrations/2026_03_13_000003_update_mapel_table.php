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
        Schema::table('mapel', function (Blueprint $table) {
            $table->dropColumn(['guru', 'kategori', 'jam']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mapel', function (Blueprint $table) {
            $table->string('guru')->after('nama');
            $table->string('kategori')->after('guru');
            $table->integer('jam')->after('kategori');
        });
    }
};
