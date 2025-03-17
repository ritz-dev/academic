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
        Schema::create('exam_teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_schedule_id')->constrained('exam_schedules')->onDelete('cascade'); // Links to the exam schedule
            $table->string('teacher_id'); // Links to the teacher
            $table->string('role')->nullable();
            $table->string('status')->default('not_answered');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_teacher_assignments');
    }
};
