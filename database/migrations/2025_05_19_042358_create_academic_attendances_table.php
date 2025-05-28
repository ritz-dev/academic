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
            $table->string('attendee_slug');
            $table->string('schedule_slug');

            $table->enum('attendee_type', ['student', 'teacher']);
            $table->enum('status', ['present', 'absent', 'late', 'excused']);
            $table->datetime('date');
            $table->text('remark')->nullable();

            $table->string('previous_hash')->nullable();
            $table->string('hash')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('schedule_slug')->references('slug')->on('daily_schedules')->onDelete('cascade');

            $table->unique(['attendee_type', 'attendee_slug', 'schedule_slug'], 'attendee_schedule_unique');
            $table->index(['attendee_type', 'attendee_slug'], 'attendee_type_index');
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
