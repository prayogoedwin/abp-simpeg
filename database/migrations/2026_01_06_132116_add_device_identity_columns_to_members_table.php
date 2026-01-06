<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('identity')->nullable()->after('status');
            $table->string('identity_name')->nullable()->after('identity');
            $table->string('device_name')->nullable()->after('identity_name');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['identity', 'identity_name', 'device_name']);
        });
    }
};