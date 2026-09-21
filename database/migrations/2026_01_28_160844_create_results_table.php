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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->string('fd')->nullable();
            $table->date('result_date');
            $table->time('result_time');
            $table->string('first_prize', 10)->nullable();
            $table->string('second_prize', 10)->nullable();
            $table->string('third_prize', 10)->nullable();
            $table->string('fourth_prize', 10)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
