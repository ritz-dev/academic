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
        Schema::create('daily_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('academic_class_section_slug');
            $table->string('subject_slug')->nullable();
            $table->string('teacher_slug')->nullable();

            $table->string('teacher_name')->nullable();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->enum('holiday_type', ['public', 'weekly', 'none'])->default('none');
            $table->enum('type', ['class', 'break'])->default('class');
            $table->text('note')->nullable();
            $table->string('academic_info');

            // Foreign keys
            $table->foreign('subject_slug')->references('slug')->on('subjects')->onDelete('cascade')->nullOnDelete();
            $table->foreign('academic_class_section_slug')->references('slug')->on('academic_class_sections')->onDelete('cascade');

            $table->index(['academic_class_section_slug', 'date']);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_schedules');
    }
};
