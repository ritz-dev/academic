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
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('student_slug');
            $table->string('academic_class_section_slug');
            
            // Enrollment details
            $table->string('student_name')->nullable();
            $table->integer('roll_number')->nullable();
            $table->date('admission_date')->nullable();
            $table->enum('enrollment_type', ['new', 'transfer', 're-admission'])->default('new');
            $table->string('previous_school')->nullable();
            $table->date('graduation_date')->nullable();
            $table->enum('status', ['active', 'graduated', 'transferred', 'dropped'])->default('active');
            $table->text('remarks')->nullable();
            $table->string('academic_info')->nullable();

            // Foreign keys
            $table->foreign('academic_class_section_slug')->references('slug')->on('academic_class_sections')->onDelete('cascade');

            // Unique constraint to avoid duplicate enrollment in same year
            $table->unique(['student_slug', 'academic_class_section_slug'], 'unique_student_enrollment');

            // timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
