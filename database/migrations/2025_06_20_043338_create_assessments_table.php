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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('teacher_slug');
            $table->string('academic_class_section_slug');
            $table->string('subject_slug');
            $table->enum('type', ['quiz', 'test', 'exam', 'assignment']);
            $table->unsignedBigInteger('date');
            $table->unsignedBigInteger('due_date')->nullable();
            $table->integer('max_marks');
            $table->integer('min_marks');
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('academic_class_section_slug')->references('slug')->on('academic_class_sections')->onDelete('cascade');
            $table->foreign('subject_slug')->references('slug')->on('subjects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
