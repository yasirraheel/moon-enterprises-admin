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
        Schema::dropIfExists('user_credits');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('user_credits', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->integer('credits')->default(0);
            $table->integer('used_credits')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id']);
        });
    }
};
