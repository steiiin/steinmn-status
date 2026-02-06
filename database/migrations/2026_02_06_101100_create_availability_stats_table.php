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
        Schema::create('availability_stats', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->date('date');                                // Europe/Berlin Tag
            $table->integer('avg_response_time_ms')->nullable(); // aus UP-samples
            $table->decimal('availability_p', 5, 4);             // 0..1
            $table->unsignedInteger('samples_total');            // erwartet ~480/Tag bei 3min
            $table->unsignedInteger('samples_up');
            $table->decimal('coverage_p', 5, 4);                 // samples_total / 480.0
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_stats');
    }
};
