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
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->foreignId('exam_schedules_id')->constrained('exam_schedules')->onDelete('cascade');
            $table->string('subject');
            $table->integer('mark');
            $table->string('grade');
            $table->date('date');
            $table->string('result');
            $table->string('hash');
            $table->string('previous_hash');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
