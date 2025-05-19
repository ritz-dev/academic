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
            $table->date('date');
            $table->foreignId('academic_class_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->enum('holiday_type', ['public', 'weekly', 'none'])->default('none');
            $table->enum('type', ['class', 'break'])->default('class');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['academic_class_section_id', 'date']);
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
