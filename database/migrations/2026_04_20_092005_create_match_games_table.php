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
        Schema::create('match_games', function (Blueprint $table) {
            $table->id();                                                                  // Auto-increment primary key
            $table->foreignId('winner_id')->constrained('participants')->cascadeOnDelete(); // FK → participants.id
            $table->foreignId('loser_id')->constrained('participants')->cascadeOnDelete();  // FK → participants.id
            $table->integer('winner_score');                                               // Score of the winner
            $table->integer('loser_score');                                                // Score of the loser
            $table->string('game_type')->nullable();                                       // "UNO", "Chess" (optional)
            $table->timestamp('played_at')->useCurrent();                                  // When the match was played
            $table->timestamps();                                                          // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_games');
    }
};
