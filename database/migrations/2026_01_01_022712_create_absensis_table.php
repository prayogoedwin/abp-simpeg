<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->unsignedBigInteger('instansi_id')->nullable();
            $table->date('tanggal')->nullable();
            
            // Jadwal (snapshot)
            $table->time('jadwal_jam_masuk')->nullable();
            $table->time('jadwal_jam_pulang')->nullable();
            
            // Actual
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            
            // Status: hadir, alpha, izin, sakit, cuti, terlambat, libur
            $table->string('status')->nullable();
            
            // Keterlambatan & pulang awal (menit)
            $table->integer('telat_menit')->nullable();
            $table->integer('pulang_awal_menit')->nullable();
            
            // Lokasi masuk
            $table->decimal('lat_masuk', 10, 8)->nullable();
            $table->decimal('lng_masuk', 11, 8)->nullable();
            $table->decimal('jarak_lokasi_masuk', 10, 2)->nullable(); // dalam meter
            
            // Lokasi pulang
            $table->decimal('lat_pulang', 10, 8)->nullable();
            $table->decimal('lng_pulang', 11, 8)->nullable();
            $table->decimal('jarak_lokasi_pulang', 10, 2)->nullable(); // dalam meter
            
            // Foto
            $table->string('foto_masuk')->nullable();
            $table->string('foto_pulang')->nullable();
            
            // Device info
            $table->json('device_info')->nullable();
            
            // Keterangan & approval
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Index tanpa constraint
            $table->index('member_id');
            $table->index('instansi_id');
            $table->index('tanggal');
            $table->index(['member_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};