<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Add columns to questions table
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'game_id')) {
                $table->foreignId('game_id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('questions', 'question_text')) {
                $table->string('question_text');
            }
            if (!Schema::hasColumn('questions', 'points')) {
                $table->integer('points')->default(10);
            }
        });

        // Add columns to answers table
        Schema::table('answers', function (Blueprint $table) {
            if (!Schema::hasColumn('answers', 'question_id')) {
                $table->foreignId('question_id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('answers', 'answer_text')) {
                $table->string('answer_text');
            }
            if (!Schema::hasColumn('answers', 'is_correct')) {
                $table->boolean('is_correct')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Remove columns from questions table
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'game_id')) {
                $table->dropForeign(['game_id']);
                $table->dropColumn('game_id');
            }
            $table->dropColumnIfExists('question_text');
            $table->dropColumnIfExists('points');
        });

        // Remove columns from answers table
        Schema::table('answers', function (Blueprint $table) {
            if (Schema::hasColumn('answers', 'question_id')) {
                $table->dropForeign(['question_id']);
                $table->dropColumn('question_id');
            }
            $table->dropColumnIfExists('answer_text');
            $table->dropColumnIfExists('is_correct');
        });
    }
};