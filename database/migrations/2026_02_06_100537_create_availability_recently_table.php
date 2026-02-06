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
        Schema::create('availability_recently', function (Blueprint $table) {
            $table->id();
            $table->dateTimeTz('probed_at');
            $table->boolean('is_available');
            $table->integer('response_time_ms')->nullable();
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->string('error_kind')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_recently');
    }
};
