<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posisis', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // satpam, cleaning service, driver, dll
            $table->string('kode')->unique()->nullable();
            $table->text('deskripsi')->nullable();
            $table->decimal('gaji_pokok', 12, 2)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posisis');
    }
};