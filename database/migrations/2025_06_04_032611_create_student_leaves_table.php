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
        Schema::create('student_leaves', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            $table->string('student_enrollment_slug');
            $table->string('academic_class_section_slug');
            $table->string('weekly_schedule_slug');
            $table->string('student_name');
            $table->string('academic_info');

            $table->date('date');

            $table->enum('leave_type', ['sick', 'personal', 'vacation', 'emergency', 'other'])->default('other');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('approved_by')->nullable(); // can be teacher/admin
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_enrollment_slug')->references('slug')->on('student_enrollments')->onDelete('cascade');
            $table->foreign('academic_class_section_slug')->references('slug')->on('academic_class_sections')->onDelete('cascade');
            $table->foreign('weekly_schedule_slug')->references('slug')->on('weekly_schedules')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_leaves');
    }
};
