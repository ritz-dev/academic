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
        Schema::create('exam_student_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_schedule_id')->constrained('exam_schedules')->onDelete('cascade');
            $table->string('student_id');
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
        Schema::dropIfExists('exam_student_assignments');
    }
};
