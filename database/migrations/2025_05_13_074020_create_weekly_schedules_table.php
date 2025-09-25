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
            $table->string('academic_class_section_slug');
            $table->string('subject_slug')->nullable();
            $table->string('teacher_slug')->nullable();

            $table->string('teacher_name')->nullable();
            $table->string('subject_name')->nullable();
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('type', ['class', 'break'])->default('class');
            $table->string('academic_info');
            $table->timestamps();

            // Foreign keys
            $table->foreign('subject_slug')->references('slug')->on('subjects')->onDelete('cascade')->nullOnDelete();
            $table->foreign('academic_class_section_slug')->references('slug')->on('academic_class_sections')->onDelete('cascade');

            // Optional: to prevent duplicates
            $table->unique(['academic_class_section_slug', 'day_of_week', 'start_time'], 'unique_weekly_schedule');

            // Optional: indexing for performance
            $table->index(['academic_class_section_slug', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_schedules');
    }
};