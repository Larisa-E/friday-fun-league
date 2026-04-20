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
        Schema::create('participants', function (Blueprint $table) {
            $table->id();                                      // Auto-increment primary key
            $table->string('name')->unique();                  // Player name, must be unique
            $table->string('avatar_emoji')->nullable();        // Optional emoji like 🐉 or 🎮
            $table->integer('points')->default(0);             // Total points earned (3 per win)
            $table->integer('wins')->default(0);               // Total wins
            $table->integer('losses')->default(0);             // Total losses
            $table->integer('matches_played')->default(0);     // wins + losses combined
            $table->timestamps();                              // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
