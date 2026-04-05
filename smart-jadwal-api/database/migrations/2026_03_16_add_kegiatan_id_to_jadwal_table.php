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
        Schema::table('jadwal', function (Blueprint $table) {
            // First, drop the existing mapel_id foreign key
            $table->dropForeign('jadwal_mapel_id_foreign');
            // Make mapel_id nullable
            $table->unsignedBigInteger('mapel_id')->nullable()->change();
            // Re-create the foreign key with cascade delete
            $table->foreign('mapel_id')->references('id')->on('mapel')->onDelete('cascade');
            // Add kegiatan_id foreign key
            $table->foreignId('kegiatan_id')->nullable()->after('mapel_id')->constrained('kegiatan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            $table->dropForeign('jadwal_kegiatan_id_foreign');
            $table->dropColumn('kegiatan_id');
            $table->dropForeign('jadwal_mapel_id_foreign');
            $table->unsignedBigInteger('mapel_id')->nullable(false)->change();
            $table->foreign('mapel_id')->references('id')->on('mapel')->onDelete('cascade');
        });
    }
};
