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
        Schema::create('time_tables', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('title')->nullable();
            $table->string('academic_class_id')->nullable();
            $table->string('section_id')->nullable();
            $table->string('subject_id')->nullable();
            $table->string('teacher_id')->nullable();
            $table->string('room')->nullable();
            $table->date('date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('type',['Lecture','Holiday','Exam','Break-Time']);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['academic_class_id', 'section_id', 'subject_id', 'teacher_id', 'date', 'start_time', 'end_time'], 'unique_timetable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_tables');
    }
};
