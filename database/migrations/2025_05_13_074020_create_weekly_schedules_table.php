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
            $table->string('slug')->unique();
            $table->foreignId('academic_class_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('teacher_id')->nullable();
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('type', ['class', 'break'])->default('class');
            $table->timestamps();
            $table->softDeletes();
            // Optional: to prevent duplicates
            $table->unique(['academic_class_section_id', 'subject_id', 'day_of_week', 'start_time', 'type'], 'unique_weekly_schedule');
            // Optional: indexing for performance
            $table->index(['academic_class_section_id', 'day_of_week']);
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
