<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('actions', function (Blueprint $table) {
            // node_id was NOT NULL; now nullable since we use polymorphic interactable instead
            $table->dropForeign(['node_id']);
            $table->unsignedBigInteger('node_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('actions', function (Blueprint $table) {
            $table->unsignedBigInteger('node_id')->nullable(false)->change();
            $table->foreign('node_id')->references('id')->on('nodes')->onDelete('cascade');
        });
    }
};
