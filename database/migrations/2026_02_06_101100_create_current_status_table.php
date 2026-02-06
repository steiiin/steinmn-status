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
        Schema::create('current_status', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // wann kam zuletzt ein Heartbeat JSON an?
            $table->dateTimeTz('last_heartbeat_at')->nullable();

            // thermal
            $table->string('thermal_range')->nullable();        // HIGH|MEDIUM|LOW
            $table->unsignedSmallInteger('thermal_temperature')->nullable();

            // hdd
            $table->boolean('hdd_a_ok')->nullable();
            $table->boolean('hdd_a_health')->nullable();
            $table->decimal('hdd_a_free_p', 5, 4)->nullable();  // 0..1

            $table->boolean('hdd_b_ok')->nullable();
            $table->boolean('hdd_b_health')->nullable();
            $table->decimal('hdd_b_free_p', 5, 4)->nullable();  // 0..1

            // core flags
            $table->boolean('encryption_ok')->nullable();
            $table->boolean('service_docker_ok')->nullable();
            $table->boolean('service_nginx_ok')->nullable();

            // containers (gesamt)
            $table->boolean('container_buero_ok')->nullable();
            $table->boolean('container_medien_ok')->nullable();
            $table->boolean('container_doku_ok')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('current_status');
    }
};
