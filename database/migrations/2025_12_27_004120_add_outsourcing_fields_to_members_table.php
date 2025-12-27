<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Foreign keys
            $table->foreignId('instansi_id')->nullable()->constrained('instansis')->nullOnDelete();
            $table->foreignId('posisi_id')->nullable()->constrained('posisis')->nullOnDelete();
            
            // Data pegawai tambahan
            $table->string('nik', 16)->nullable()->unique();
            $table->string('no_karyawan')->nullable()->unique();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->date('tanggal_kontrak_berakhir')->nullable();
            $table->enum('status_kepegawaian', ['aktif', 'nonaktif', 'cuti', 'resign'])->default('aktif');
            $table->string('foto')->nullable();
            
            // Hapus kolom yang tidak relevan (optional, sesuaikan dengan kebutuhan)
            // $table->dropColumn(['poin_terkini', 'tipe_akun']);
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['instansi_id']);
            $table->dropForeign(['posisi_id']);
            $table->dropColumn([
                'instansi_id',
                'posisi_id',
                'nik',
                'no_karyawan',
                'tanggal_lahir',
                'jenis_kelamin',
                'tanggal_masuk',
                'tanggal_kontrak_berakhir',
                'status_kepegawaian',
                'foto'
            ]);
        });
    }
};