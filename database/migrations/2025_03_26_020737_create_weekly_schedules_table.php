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
        Schema::create('weekly_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('subject_id')->nullable();
            $table->enum ('day', [1, 2, 3, 4, 5, 6, 7]);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_break')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_schedules');
    }
};
