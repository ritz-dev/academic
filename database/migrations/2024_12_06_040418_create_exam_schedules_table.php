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
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            $table->string('subject');
            $table->date('date');
            $table->string('start_time');
            $table->string('end_time');
            $table->unique(['subject', 'date','start_time','end_time']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};
