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
        Schema::create('assessment_results', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            $table->string('previous_hash')->nullable();
            $table->string('hash')->nullable();

            $table->string('assessment_slug');
            $table->string('student_slug'); // Adjust type if needed
            $table->integer('marks_obtained')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'graded', 'reviewed'])->default('pending');
            $table->string('graded_by')->nullable(); // Nullable in case grading is not done
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();

            $table->foreign('assessment_slug')->references('slug')->on('assessments')->onDelete('cascade');
            $table->unique(['assessment_slug', 'student_slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_results');
    }
};
