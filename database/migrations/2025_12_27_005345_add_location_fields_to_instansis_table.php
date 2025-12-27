<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instansis', function (Blueprint $table) {
            $table->decimal('lat', 10, 8)->nullable()->after('alamat');
            $table->decimal('lng', 11, 8)->nullable()->after('lat');
            $table->string('google_maps_link')->nullable()->after('lng');
        });
    }

    public function down(): void
    {
        Schema::table('instansis', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'google_maps_link']);
        });
    }
};