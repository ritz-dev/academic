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
        Schema::create('academic_attendances', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('weekly_schedule_slug');
            $table->string('subject');
            $table->string('academic_class_section_slug');
            $table->string('academic_info')->nullable();

            //Attedance Person
            $table->string('attendee_slug');
            $table->string('attendee_name');
            $table->enum('attendee_type', ['student', 'teacher']);
            $table->enum('status', ['present', 'absent', 'late', 'excused']);
            $table->enum('attendance_type',['class', 'exam', 'event'])->default('class');
            $table->string('approved_slug')->nullable();
            $table->unsignedBigInteger('date');
            $table->datetime('modified')->nullable();
            $table->string('modified_by')->nullable();
            $table->text('remark')->nullable();

            $table->string('previous_hash')->nullable();
            $table->string('hash')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('weekly_schedule_slug')->references('slug')->on('weekly_schedules')->onDelete('cascade');
            $table->foreign('academic_class_section_slug')->references('slug')->on('academic_class_sections')->onDelete('cascade');
            // $table->unique(['attendee_type', 'attendee_slug', 'weekly_schedule_slug', 'date'], 'attendee_schedule_unique');
            // $table->index(['attendee_type', 'attendee_slug'], 'attendee_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_attendances');
    }
};