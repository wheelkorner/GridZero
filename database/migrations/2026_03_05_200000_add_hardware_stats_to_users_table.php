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
        Schema::table('users', function (Blueprint $blueprint) {
            $blueprint->integer('cpu')->default(800)->after('level'); // in MHz
            $blueprint->integer('ram')->default(512)->after('cpu'); // in MB
            $blueprint->integer('ssd')->default(100)->after('ram'); // in percentage (health)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['cpu', 'ram', 'ssd']);
        });
    }
};
