<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jam_kerjas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instansi_id')->nullable();
            $table->unsignedBigInteger('jenis_pegawai_id')->nullable();
            $table->string('jenis_jam_kerja')->default('NORMAL'); // NORMAL, SHIFT 1, SHIFT 2, SHIFT 3, LONGSHIFT
            $table->time('jam_masuk');
            $table->time('jam_pulang');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Index tanpa foreign key constraint
            $table->index('instansi_id');
            $table->index('jenis_pegawai_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jam_kerjas');
    }
};