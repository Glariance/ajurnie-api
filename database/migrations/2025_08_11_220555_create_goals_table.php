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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->default(0);
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('name');
            $table->string('email');
            $table->string('age');
            $table->string('gender');
            $table->string('height');
            $table->string('current_weight');
            $table->string('medical_conditions');
            $table->string('fitness_goal');
            $table->string('target_weight');
            $table->string('deadline');
            $table->string('activity_level');
            $table->string('workout_style');
            $table->string('dietary_preferences');
            $table->longText('food_allergies');
            $table->string('plan_generated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
