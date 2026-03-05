<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('node_id')->constrained()->onDelete('cascade');
            $table->string('interactable_type')->nullable(); // Ex: 'App\Models\Node' ou 'App\Models\Npc'
            $table->unsignedBigInteger('interactable_id')->nullable();
            $table->string('type');
            $table->string('status');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->index(['user_id', 'ends_at']);
            $table->timestamps();
            $table->index(['interactable_type', 'interactable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actions');
    }
};
